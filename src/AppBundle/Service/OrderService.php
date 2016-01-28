<?php
namespace AppBundle\Service;

use AppBundle\Entity\CartItemEntity;
use AppBundle\Entity\OrderEntity;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class OrderService {
	const STATUS_OK = true;
	const STATUS_ERROR = null;
	const STATUS_NOT_FOUND = false;

	const FILTER_ALL = 'all';

	/** @var \Twig_Environment */
	protected $twig;

	/** @var array */
	protected $sizes = [];

	/** @var Session */
	protected $session;

	/** @var \Swift_Mailer */
	protected $swift_mailer;

	/** @var EntityManagerInterface */
	protected $entity_manager;

	public function __construct(
		EntityManagerInterface $entity_manager, \Swift_Mailer $swift_mailer,
		Session $session, \Twig_Environment $twig, array $sizes
	) {
		$this->entity_manager = $entity_manager;
		$this->swift_mailer = $swift_mailer;
		$this->session = $session;
		$this->twig = $twig;
		$this->sizes = $sizes;
	}

	protected function loadCart() {
		if (!$this->session->has('cart')) {
			$this->session->set('cart', ['total' => 0, 'items' => []]);
		}

		$cart = $this->session->get('cart');

		if (!isset($cart['total'], $cart['items'])) {
			$this->session->set('cart', ['total' => 0, 'items' => []]);
			$cart = $this->session->get('cart');
		}

		return $cart;
	}

	protected function storeCart(array $cart) {
		$total = 0;

		if (empty($cart['items'])) {
			$cart = ['total' => 0, 'items' => []];
		}

		foreach ($cart['items'] as $sku => $item) {
			foreach ($item['sizes'] as $size => $size_data) {
				if ($size_data['quantity'] > 0) {
					$total += $size_data['quantity'] * $item['price'];
					continue;
				}

				unset($cart['items'][$sku]['sizes'][$size]);
			}

			if (empty($item['sizes'])) {
				unset($cart['items'][$sku]);
			}
		}

		$cart['total'] = $total;

		$this->session->set('cart', $cart);
	}

	public function addIntoCartFromTheForm(FormInterface $form) {
		$cart = $this->loadCart();

		/** @var CartItemEntity $entity */
		$entity = $form->getData();
		$sku = $entity->getSku();

		if (isset($cart['items'][$sku]['sizes'][$entity->size]['quantity'])) {
			$entity->quantity += $cart['items'][$sku]['sizes'][$entity->size]['quantity'];
		}

		if ($entity->quantity > 0) {
			$cart['items'][$sku]['title'] = $entity->getTitle();
			$cart['items'][$sku]['price'] = $entity->getPrice();
			$cart['items'][$sku]['sizes'][$entity->size]['title'] = $this->sizes[$entity->size];
			$cart['items'][$sku]['sizes'][$entity->size]['quantity'] = $entity->quantity;
		}

		$this->storeCart($cart);
	}

	public function removeFromCart($sku, $size) {
		$cart = $this->loadCart();
		unset($cart['items'][$sku]['sizes'][$size]);
		$this->storeCart($cart);
	}

	public function cartIsEmpty() {
		return $this->loadCart()['total'] <= 0;
	}

	public function finishOrderViaTheForm(FormInterface $form) {
		$cart = $this->loadCart();

		/** @var OrderEntity $entity */
		$entity = $form->getData();
		$entity->cart = $cart;

		try {
			$this->entity_manager->persist($entity);
			$this->entity_manager->flush();

			$data = [
				'name'  => $entity->name,
				'email' => $entity->email,
				'cart'  => $entity->cart,
				'note'  => $entity->note
			];

			$message = new \Swift_Message('Objednávka z webu Symfony.cz');
			$message->addFrom('symfony-eshop@zemistr.eu');
			$message->addTo($entity->email);
			$message->setBody($this->twig->render('AppBundle:Cart:email.html.twig', $data), 'text/html');
			$this->swift_mailer->send($message, $failed);

			$message = new \Swift_Message('Nová objednávka z webu Symfony.cz');
			$message->addFrom('symfony-eshop@zemistr.eu');
			$message->addTo('symfony-eshop@zemistr.eu');
			$message->setBody($this->twig->render('AppBundle:Cart:emailAdmin.html.twig', $data), 'text/html');
			$this->swift_mailer->send($message, $failed);

			$this->storeCart([]);

			return self::STATUS_OK;
		}
		catch (\Exception $e) {
		}

		return self::STATUS_ERROR;
	}

	public function fetchOrdersWithStatus($status = self::FILTER_ALL) {
		$repository = $this->entity_manager->getRepository(OrderEntity::class);

		if ($status === 'all') {
			return $repository->findAll();
		}

		return $repository->findBy(['status' => +$status]);
	}

	public function changeOrderStatus($id, $status) {
		$status = +$status;

		if ($status < -1 || $status > 2) {
			return self::STATUS_ERROR;
		}

		$entity = $this->entity_manager->getRepository(OrderEntity::class)->find($id);

		if (!$entity) {
			return self::STATUS_NOT_FOUND;
		}

		$entity->status = $status;
		$this->entity_manager->persist($entity);
		$this->entity_manager->flush();

		return self::STATUS_OK;
	}
}
