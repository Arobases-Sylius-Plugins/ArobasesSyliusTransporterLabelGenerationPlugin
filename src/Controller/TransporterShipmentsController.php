<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Controller;

use Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo\ColissimoRequest;
use Arobases\SyliusTransporterLabelGenerationPlugin\Form\Type\TransporterProductCodeType;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingMethodRepository;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Addressing\Address;

final class TransporterShipmentsController extends AbstractController
{
    public function __construct(
        private TransporterRepository $transporterRepository,
        private ShipmentRepositoryInterface $shipmentRepository,
        private EntityManagerInterface $entityManager,
        private ColissimoRequest $colissimoRequest,
        private OrderRepository $orderRepository,
    ) {}

    public function renderUpdateForm(Request $request): Response
    {
        $transporterId = $request->query->get('transporterId');
        $shipmentId = $request->query->get('shipmentId');
        $orderId = $request->query->get('orderId');

        if ($transporterId && $transporterId !== '') {
            $transporter = $this->transporterRepository->find($transporterId);
        } else {
            $transporter = null;
        }

        $form = $this->createForm(TransporterProductCodeType::class, null, ['shipmentId' => $shipmentId, 'transporterId' => $transporterId, 'orderId' => $orderId]);

        return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/Show/_product_codes_form.html.twig', [
            'form' => $form->createView(),
            'transporter' => $transporter,
        ]);
    }

    public function updateShipmentProductCode(Request $request): RedirectResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');

        $shipmentId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['shipmentId'];
        $transporterCode = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['transporterCode'];
        $transporterId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['transporterId'];
        $orderId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['orderId'];
        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->find($shipmentId);
        if (!$shipment) {
            $flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.error');
        } else {
            $shipment->setTransporterCode($transporterCode);
            $this->entityManager->persist($shipment);
            $this->entityManager->flush();

            // choose pick up point
            if ($transporterCode === 'A2P' || $transporterCode === 'BPR' || $transporterCode === 'CDI' || $transporterCode === 'CMT' || $transporterCode === 'BDP' || $transporterCode === 'PCS' || $transporterCode === 'ACP') {
                return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_render_pickup_point_map', ['shipmentId' => $shipment->getId(), 'orderId' => $orderId, 'transporterCode' => $transporterCode]);
            }
            $flashBag->add('update-shipping-method-success', 'arobases_sylius_transporter_label_generation_plugin.flashbag.error');
        }

        return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_show', ['id' => $transporterId]);
    }

    public function renderPickupPointMap(Request $request): Response
    {
        /** @var Order $order */
        $order = $this->orderRepository->find($request->query->get('orderId'));
        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->find($request->query->get('shipmentId'));
        $transporterCode = $request->query->get('transporterCode');

        $newAddress = new Address();
        $addressForm = $this->createForm(AddressType::class, $newAddress);
        $addressForm->handleRequest($request);

        // update order address
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $this->entityManager->persist($newAddress);
            $order->setShippingAddress($newAddress);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            if ($request->request->get('is_pickup_point') === 'true') {
                return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_show', ['id' => $shipment->getTransporter()->getId()]);
            }

            return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/choose_pickup_point.html.twig', [
                'order' => $order,
                'shipment' => $shipment,
                'addressForm' => $addressForm->createView(),
                'transporterCode' => $transporterCode,
            ]);
        }

        return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/choose_pickup_point.html.twig', [
            'order' => $order,
            'shipment' => $shipment,
            'addressForm' => $addressForm->createView(),
            'transporterCode' => $transporterCode,
        ]);
    }

    public function choosePickPointAjax(Request $request): JsonResponse
    {
        $orderId = $request->request->get('order_id');
        $shipmentId = $request->request->get('shipment_selected_code');
        $transporterCode = $request->request->get('transporter_code');
        if (!$orderId || !$shipmentId) {
            return new JsonResponse('La sélection du point relais a échoué', Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = $this->orderRepository->find($orderId);
        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->find($shipmentId);
        if (!$order || !$shipment) {
            return new JsonResponse('La sélection du point relais a échoué', Response::HTTP_BAD_REQUEST);
        }
        $transporter = $shipment->getTransporter();
        if (!$transporter) {
            return new JsonResponse('La sélection du point relais a échoué', Response::HTTP_BAD_REQUEST);
        }

        $listPoints = [];
        if (strtolower($shipment->getTransporter()->getName()) === 'colissimo') { //colissimo
            $colissimoResponse = $this->colissimoRequest->getPickupPoints($order, 1, $transporter);
            if ($colissimoResponse['errorCode'] !== 0) {
                return new JsonResponse($colissimoResponse['errorMessage'], Response::HTTP_BAD_REQUEST);
            }
            $allPickupPoint = $colissimoResponse['listePointRetraitAcheminement'];
            $pointsTemp = [];

            foreach ($allPickupPoint as $pickupPoint) {
                if ($pickupPoint['typeDePoint'] === $transporterCode) {
                    $pointsTemp[] = $pickupPoint;
                }
            }
            $countPoints = 0;
            $points = [];
            if (count($pointsTemp)) {
                foreach ($pointsTemp as $point) {
                    ++$countPoints;
                    $openingScheduleList = [];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureDimanche'],
                        'jour' => 7,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureSamedi'],
                        'jour' => 6,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureVendredi'],
                        'jour' => 5,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureJeudi'],
                        'jour' => 4,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureMercredi'],
                        'jour' => 3,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureMardi'],
                        'jour' => 2,
                    ];
                    $openingScheduleList[] = [
                        'horairesAsString' => $point['horairesOuvertureLundi'],
                        'jour' => 1,
                    ];
                    $point['openingSchedulesList'] = $openingScheduleList;

                    $point['icon'] = '/bundles/arobasessyliustransporterlabelgenerationplugin/images/picto-colissimo.png';
                    $point['icon_md'] = '/bundles/arobasessyliustransporterlabelgenerationplugin/images/picto-colissimo.png';
                    $points[] = $point;
                }
            }
        }

        $listPoints['listpoints'] = $points;

        return new JsonResponse($listPoints);
    }
}
