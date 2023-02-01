<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Controller;

use Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo\ColissimoRequest;
use Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo\MTOM_ResponseReader;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Label;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\LabelItem;
use Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Transporter;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\LabelItemRepository;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\LabelRepository;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderItemRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Sylius\Component\Order\Model\Adjustment;
use Sylius\Component\Order\Model\Order;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class OrderLabelController extends AbstractController
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private EntityManagerInterface $entityManager,
        private OrderItemRepository $orderItemRepository,
        private LabelRepository $labelRepository,
        private LabelItemRepository $labelItemRepository,
        private TransporterRepository $transporterRepository,
        private ColissimoRequest $colissimoRequest,
        private ChannelContextInterface $channelContext,
        private ShipmentRepositoryInterface $shipmentRepository,
        private string $basePath,
        private string $labelColissimoUploadPath
    ) {
    }

    public function renderOrderDetails(Request $request): JsonResponse
    {
        $orderNumber = $request->query->get('number');
        if (!$orderNumber) {
            return new JsonResponse('order number missing', Response::HTTP_BAD_REQUEST);
        }

        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['number' => $orderNumber]);
        if (!$order) {
            return new JsonResponse('order lost', Response::HTTP_BAD_REQUEST);
        }

        $shippings = $order->getAdjustments('shipping');
        $shippingCosts = 0;
        /** @var Adjustment $shipping */
        foreach ($shippings as $shipping) {
            $shippingCosts += $shipping->getAmount();
        }

        $totalWeight = 0;
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $totalWeight += $item->getVariant()->getWeight();
        }

        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->findOneBy(['order' => $order]);
        $transporterId = $shipment->getTransporter()?->getId();

        $html = $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/Show/_order_details.html.twig', ['order' => $order, 'shippingCosts' => $shippingCosts, 'totalWeight' => $totalWeight, 'transporterId' => $transporterId])->getContent();

        return new JsonResponse(['html' => $html]);
    }

    public function generateLabelAjax(Request $request): JsonResponse
    {
        $totalWeight = $request->request->get('total_weight');
        $transporterId = $request->request->get('transporter');
        $orderId = $request->request->get('order_id');
        if ($totalWeight === null || $totalWeight === '' || $transporterId === null || $transporterId === '' || $orderId === null || $orderId === '') {
            return new JsonResponse('data missing', Response::HTTP_BAD_REQUEST);
        }

        /** @var Order $order */
        $order = $this->orderRepository->find((int) $orderId);
        /** @var Transporter $transporter */
        $transporter = $this->transporterRepository->find((int) $transporterId);

        if (!$order || !$transporter) {
            return new JsonResponse("order or transporter missing", Response::HTTP_BAD_REQUEST);
        }

        //create label
        $label = new Label();
        $label->setTrackingNumber('00000');
        $label->setTotalWeight((float) $totalWeight);
        $label->setRelatedOrder($order);
        $label->setPath(null);
        $this->entityManager->persist($label);

        // find item rows
        $itemIds = [];
        foreach ($request->request as $key => $data) {
            if (strpos($key, 'weight_') !== false) {
                $itemIds[] = str_replace('weight_', '', $key);
            }
        }
        // create label items
        foreach ($itemIds as $id) {
            $orderItem = $this->orderItemRepository->find($id);

            $labelItem = new LabelItem();
            $labelItem->setOrderItem($orderItem);
            $labelItem->setQuantity((int) $request->request->get('quantity_' . $id));
            $labelItem->setWeight((float) $request->request->get('weight_' . $id));
            $label->addLabelItem($labelItem);
            $this->entityManager->persist($labelItem);
        }
        $this->entityManager->flush();

        //generate label
        if ($transporter->getName() === 'colissimo') {
            $now = new \DateTime();
            $depositDate = $now->modify('+5 day')->format('Y-m-d');
            $response = $this->colissimoRequest->generateLabel($this->channelContext->getChannel(), $label, $transporter, $transporter->getDefaultOutputPrintingType(), $depositDate);
            $params = null;
            if (array_key_exists('response', $response) && !$response['response']) {
                return new JsonResponse('no response', Response::HTTP_BAD_REQUEST);
            }
            if (array_key_exists('params', $response) && $response['params']) {
                $params = $response['params'];
            }

            //+ Parse Web Service Response
            $parseResponse = new MTOM_ResponseReader($response['response']);
            $resultat_tmp = $parseResponse->soapResponse;
            $soap_result = $resultat_tmp['data'];
            $error_code = explode('<id>', $soap_result);
            $error_code = explode('</id>', $error_code[1]);
            $error_message = explode('<messageContent>', $soap_result);
            $error_message = explode('</messageContent>', $error_message[1]);

            //+ Error handling and label saving
            if ($error_code[0] == '0') { //success
                $resultat_tmp = $parseResponse->attachments;
                $label_content = $resultat_tmp[0];
                $datas = $label_content['data'];
                $save_valid = true;

                //Save the label
                if ($params) {
                    $extension_tmp = $params['outputFormat']['outputPrintingType'];
                } else {
                    $extension_tmp = 'pdf';
                }
                $extension = strtolower(substr($extension_tmp, 0, 3));
                $pieces = explode('<parcelNumber>', $soap_result);
                $pieces = explode('</parcelNumber>', $pieces[1]);
                $parcelNumber = $pieces[0]; //Extract the parcel number
                $fileName = $this->labelColissimoUploadPath . $parcelNumber . '.' . $extension;
                $file = fopen($this->basePath . $fileName, 'a+');
                if (fwrite($file, $datas)) { //Save the label in defined folder
                    fclose($file);

                    $label->setPath($fileName);
                    $label->setTrackingNumber($parcelNumber);
                    $this->entityManager->persist($label);

                    $this->entityManager->flush();
                }
                else {
                    $save_valid = false;
                }
                // CN23
                if (count($resultat_tmp) > 1) {
                    $label_contentCN23 = $resultat_tmp[1];
                    $datasCN23 = $label_contentCN23['data'];
                    $fileNameCN23 = $this->labelColissimoUploadPath . $parcelNumber . '_cn23.' . $extension;
                    $fileCN23 = fopen($this->basePath . $fileNameCN23, 'a+');
                    if (fwrite($fileCN23, $datasCN23)) { //Save the label in defined folder
                        fclose($fileCN23);

                        $label->setPathCn23($fileNameCN23);
                        $this->entityManager->persist($label);
                        $this->entityManager->flush();
                    }
                    else {
                        $save_valid = false;
                    }
                }

                if ($save_valid) {
                    return new JsonResponse(null, Response::HTTP_OK);
                }
                return new JsonResponse("label not generated", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse($error_message[0], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('not colissimo', Response::HTTP_BAD_REQUEST);
    }

    public function renderLabelSummaryAjax(Request $request): JsonResponse
    {
        $orderId = $request->query->get('orderId');
        if (!$orderId) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $labels = $this->labelRepository->findByOrder((int) $orderId);

        $order = $this->orderRepository->find((int) $orderId);
        if (!$order) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        // check if order is sent, not sent or partially sent
        $orderSent = true;
        $countLabelItems = 0;
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $relatedLabelItems = $this->labelItemRepository->findByOrderItem($item->getId());
            $relatedLabelItemsQuantity = 0;
            /** @var LabelItem $labelItem */
            foreach ($relatedLabelItems as $labelItem) {
                $relatedLabelItemsQuantity += $labelItem->getQuantity();
                $countLabelItems += $labelItem->getQuantity();
            }
            if ($relatedLabelItemsQuantity < $item->getQuantity()) {
                $orderSent = false;
            }
        }

        $html = $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/Show/_label_summary.html.twig', ['labels' => $labels, 'orderSent' => $orderSent, 'countLabelItems' => $countLabelItems])->getContent();

        return new JsonResponse(['html' => $html]);
    }

    public function deleteLabelAjax(Request $request, int $labelId): JsonResponse
    {
        /** @var Label $label */
        $label = $this->labelRepository->find($labelId);
        if (!$label) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        @unlink($label->getPath());
        if ($label->getPathCn23())
            @unlink($label->getPathCn23());
        $this->entityManager->remove($label);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
