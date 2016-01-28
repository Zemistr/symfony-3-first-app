<?php

namespace AppBundle\Controller;

use AppBundle\Form\OrderFormType;
use AppBundle\Entity\OrderEntity;
use AppBundle\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CartController extends Controller {
	/**
	 * @Route("/cart", name="cart")
	 * @Template()
	 */
	public function indexAction() {
		$order_service = $this->get('app.service.order_service');

		if ($order_service->cartIsEmpty()) {
			return $this->redirectToRoute('catalog');
		}

		$order_form_factory = $this->get('app.factory.form_factory');
		$form = $order_form_factory->createAndHandle('order', OrderFormType::class, new OrderEntity());

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->addFlash('danger', 'Zkontrolujte, prosím, zadané údaje.');
		}

		if (!$form->isSubmitted() || !$form->isValid()) {
			return ['form' => $form->createView()];
		}

		$result = $order_service->finishOrderViaTheForm($form);

		if ($result === OrderService::STATUS_OK) {
			$this->addFlash('success', 'Objednávka byla odeslána. Děkujeme!');

			return $this->redirectToRoute('catalog');
		}

		if ($result === OrderService::STATUS_ERROR) {
			$this->addFlash('danger', 'Objednávku se nepodařilo odeslat. Zkontrolujte, prosím, zadané údaje.');
		}

		return ['form' => $form->createView()];
	}

	/**
	 * @Route("/cart/{sku}/{size}/remove", name="remove_from_cart")
	 * @param $sku
	 * @param $size
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function removeFromCartAction($sku, $size) {
		$this->addFlash('success', 'Produkt byl odebrán z košíku.');
		$this->get('app.service.order_service')->removeFromCart($sku, $size);

		return $this->redirectToRoute('cart');
	}
}
