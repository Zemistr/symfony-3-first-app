<?php
namespace AppBundle\Factory;

use AppBundle\Entity\IEntity;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormFactory {
	/** @var FormFactoryInterface */
	protected $form_factory;

	/** @var RequestStack */
	protected $request_stack;

	public function __construct(FormFactoryInterface $form_factory, RequestStack $request_stack) {
		$this->form_factory = $form_factory;
		$this->request_stack = $request_stack;
	}

	public function createAndHandle($name, $type, IEntity $entity) {
		$request = $this->request_stack->getMasterRequest();

		$form = $this->form_factory->createNamed($name, $type, $entity, ['method' => 'POST']);
		$form->handleRequest($request);

		return $form;
	}
}
