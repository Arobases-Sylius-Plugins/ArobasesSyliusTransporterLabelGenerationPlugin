<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Controller;

use Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo\ColissimoRequestInterface;
use Arobases\SyliusTransporterLabelGenerationPlugin\Form\Type\TransporterProductCodeType;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class TransporterShipmentsController extends AbstractController
{
    public function __construct(
        private TransporterRepository $transporterRepository,
        private ShipmentRepositoryInterface $shipmentRepository,
        private EntityManagerInterface $entityManager,
        private ColissimoRequestInterface $colissimoRequest,
        private OrderRepositoryInterface $orderRepository,
        private FormFactoryInterface $formFactory
    ) {}

    public function renderUpdateForm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

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
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');

        // the form is validated to check the csrf token and the product code
        $form = $this->createForm(TransporterProductCodeType::class);
        $form->handleRequest($request);
        $productCodeData = $form->isSubmitted() ? $form->getData() : [];
        $shipmentId = $productCodeData['shipmentId'] ?? null;
        $transporterCode = $productCodeData['transporterCode'] ?? null;
        $transporterId = $productCodeData['transporterId'] ?? null;
        $orderId = $productCodeData['orderId'] ?? null;
        /** @var Shipment|null $shipment */
        $shipment = $form->isSubmitted() && $form->isValid() && $shipmentId ? $this->shipmentRepository->find($shipmentId) : null;
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

        if (!$transporterId) {
            return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_index');
        }

        return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_show', ['id' => $transporterId]);
    }

    public function renderPickupPointMap(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var Order|null $order */
        $order = $this->orderRepository->find($request->query->get('orderId'));
        /** @var Shipment|null $shipment */
        $shipment = $this->shipmentRepository->find($request->query->get('shipmentId'));
        if (!$order || !$shipment) {
            throw $this->createNotFoundException();
        }
        $transporterCode = $request->query->get('transporterCode');

        $addressForm = $this->formFactory->create(AddressType::class);
        $addressForm->handleRequest($request);

        // update order address
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $address = $addressForm->getData();
            $this->entityManager->persist($address);
            $order->setShippingAddress($address);
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
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

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
