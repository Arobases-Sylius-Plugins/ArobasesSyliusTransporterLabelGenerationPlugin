<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Controller;

use Arobases\SyliusTransporterLabelGenerationPlugin\Connector\Api\Colissimo\ColissimoRequest;
use Arobases\SyliusTransporterLabelGenerationPlugin\Form\Extension\AddressTypeExtension;
use Arobases\SyliusTransporterLabelGenerationPlugin\Form\Type\TransporterProductCodeType;
use Arobases\SyliusTransporterLabelGenerationPlugin\Repository\TransporterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingMethodRepository;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\ShippingMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Tests\Arobases\SyliusTransporterLabelGenerationPlugin\Entity\Addressing\Address;

final class TransporterShippingMethodsController extends AbstractController
{
    public function __construct(
        private TransporterRepository $transporterRepository,
        private ShippingMethodRepository $shippingMethodRepository,
        private EntityManagerInterface $entityManager,
        private ColissimoRequest $colissimoRequest,
        private OrderRepository $orderRepository,
        private FlashBagInterface $flashBag
    ) {}

    public function renderUpdateForm(Request $request) {
        $transporterId = $request->query->get('transporterId');
        $shippingMethodId = $request->query->get('shippingMethodId');
        $orderId = $request->query->get('orderId');

        if ($transporterId && $transporterId !== "")
            $transporter = $this->transporterRepository->find($transporterId);
        else
            $transporter = null;

        $form = $this->createForm(TransporterProductCodeType::class, null, ['shippingMethodId' => $shippingMethodId, 'transporterId' => $transporterId, 'orderId' => $orderId]);

        return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/TransporterOrder/Grid/Field/_product_codes_form.html.twig', [
            'form' => $form->createView(),
            'transporter' => $transporter
        ]);
    }

    public function updateShippingMethodProductCode(Request $request) {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');

        $shippingMethodId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['shippingMethod'];
        $transporterCode = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['transporterCode'];
        $transporterId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['transporterId'];
        $orderId = $request->request->get('arobases_sylius_transporter_label_generation_product_code')['orderId'];
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->find($shippingMethodId);
        if (!$shippingMethod) {
            $flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.error');
        }
        else {
            $shippingMethod->setTransporterCode($transporterCode);
            $this->entityManager->persist($shippingMethod);
            $this->entityManager->flush();

            // choose pick up point
            if ($transporterCode === "A2P" || $transporterCode === "BPR" || $transporterCode === "CDI" || $transporterCode === "CMT" || $transporterCode === "BDP" || $transporterCode === "PCS" || $transporterCode === "ACP") {
                return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_render_pickup_point_map', ['shippingMethodId' => $shippingMethod->getId(), 'orderId' => $orderId, 'transporterCode' => $transporterCode]);
            }
            $flashBag->add('update-shipping-method-success', 'arobases_sylius_transporter_label_generation_plugin.flashbag.error');
        }


        return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_show', ['id' => $transporterId]);
    }

    public function renderPickupPointMap(Request $request) {
        /** @var Order $order */
        $order = $this->orderRepository->find($request->query->get('orderId'));
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->find($request->query->get('shippingMethodId'));
        $transporterCode = $request->query->get('transporterCode');
        if (!$order || !$shippingMethod) {
            $this->flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.update_shipping_method_transporter_code_pickup_point_error');

            //todo: return redirect
        }

        $newAddress = new Address();
        $addressForm = $this->createForm(AddressType::class, $newAddress);
        $addressForm->handleRequest($request);

        // update order address
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $this->entityManager->persist($newAddress);
            $order->setShippingAddress($newAddress);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            if ($request->request->get('is_pickup_point') === "true")
                return $this->redirectToRoute('arobases_sylius_transporter_label_generation_plugin_admin_transporter_show', ['id' => $shippingMethod->getTransporter()->getId()]);

            return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/Transporter/choose_pickup_point.html.twig', [
                'order' => $order,
                'shippingMethod' => $shippingMethod,
                'addressForm' => $addressForm->createView()
            ]);
        }

        return $this->render('@ArobasesSyliusTransporterLabelGenerationPlugin/Admin/Transporter/choose_pickup_point.html.twig', [
            'order' => $order,
            'shippingMethod' => $shippingMethod,
            'addressForm' => $addressForm->createView(),
            'transporterCode' => $transporterCode
        ]);
    }

    public function choosePickPointAjax(Request $request):JsonResponse {
        $orderId = $request->request->get('order_id');
        $shippingMethodId = $request->request->get('shipping_method_selected_code');
        $transporterCode = $request->request->get('transporter_code');
        if (!$orderId || !$shippingMethodId) {
            $this->flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.update_shipping_method_transporter_code_pickup_point_error');
            //todo: return redirect
        }
        /** @var Order $order */
        $order = $this->orderRepository->find($orderId);
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->find($shippingMethodId);
        if (!$order || !$shippingMethod) {
            $this->flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.update_shipping_method_transporter_code_pickup_point_error');

            //todo: return redirect
        }
        $transporter = $shippingMethod->getTransporter();
        if (!$transporter) {
            $this->flashBag->add('update-shipping-method-error', 'arobases_sylius_transporter_label_generation_plugin.flashbag.update_shipping_method_transporter_code_pickup_point_error');

            //todo: return redirect
        }

        $listPoints = [];
        if(strtolower($shippingMethod->getTransporter()->getName()) === "colissimo") //colissimo
        {
            $colissimoResponse = $this->colissimoRequest->getPickupPoints($order, 1, $transporter);
            if ($colissimoResponse['errorCode'] !== 0) {
                //todo: flashbag + redirect avec l'erreur $colissimoResponse['errorMessage']
                $this->flashBag->add('update-shipping-method-error', $colissimoResponse['errorMessage']);
            }
            $allPickupPoint = $colissimoResponse['listePointRetraitAcheminement'];
            $pointsTemp = [];

            foreach ($allPickupPoint as $pickupPoint) {
                if ($pickupPoint['typeDePoint'] === $transporterCode) {
                    $pointsTemp[] = $pickupPoint;
                }
            }
            $countPoints = 0;
            $points = array();
            if(count($pointsTemp)){
                foreach ( $pointsTemp as $point ) {
                    $countPoints++;
                    $openingScheduleList = array();
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureDimanche'],
                        "jour" => 7,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureSamedi'],
                        "jour" => 6,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureVendredi'],
                        "jour" => 5,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureJeudi'],
                        "jour" => 4,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureMercredi'],
                        "jour" => 3,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureMardi'],
                        "jour" => 2,
                    );
                    $openingScheduleList[] = array(
                        "horairesAsString" => $point['horairesOuvertureLundi'],
                        "jour" => 1,
                    );
                    $point['openingSchedulesList'] = $openingScheduleList;

                    $point['icon'] = "/bundles/arobasessyliustransporterlabelgenerationplugin/images/picto-colissimo.png";
                    $point['icon_md'] = "/bundles/arobasessyliustransporterlabelgenerationplugin/images/picto-colissimo.png";
                    $points[] = $point;
                }
            }
        }

        $listPoints['listpoints'] = $points;
        return new JsonResponse($listPoints);

    }
}