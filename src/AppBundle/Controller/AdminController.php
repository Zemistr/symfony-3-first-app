<?php

namespace AppBundle\Controller;

use AppBundle\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller {
	/**
	 * @Route("/login", name="login_route")
	 * @Template()
	 */
	public function loginAction() {
		$authentication_utils = $this->get('security.authentication_utils');
		$error = $authentication_utils->getLastAuthenticationError();
		$username = $authentication_utils->getLastUsername();

		if ($error) {
			$translator = $this->get('translator');
			$this->addFlash('danger', $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security'));
		}

		return [
			'username' => $username
		];
	}

	/**
	 * @Route("/login-check", name="login_check")
	 */
	public function loginCheckAction() {
	}

	/**
	 * @Route("/logout", name="logout")
	 */
	public function logoutAction() {
	}

	/**
	 * @Route("/admin/{status}", name="admin")
	 * @Template()
	 */
	public function ordersAction($status = OrderService::FILTER_ALL) {
		$orders = $this->get('app.service.order_service')->fetchOrdersWithStatus($status);

		return [
			'orders' => $orders,
			'status' => $status
		];
	}

	/**
	 * @Route("/admin/{status}/change/{id}/to/{new_status}", name="change_status")
	 */
	public function changeStatusAction($status, $id, $new_status) {
		$result = $this->get('app.service.order_service')->changeOrderStatus($id, $new_status);

		if ($result === OrderService::STATUS_NOT_FOUND) {
			$this->addFlash('danger', 'Objednávka nebyla nalezena.');
		}

		if ($result === OrderService::STATUS_OK) {
			$this->addFlash('success', 'Stav objednávky byl změněn');
		}

		return $this->redirectToRoute('admin', ['status' => $status]);
	}
}
