<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFormType extends AbstractType {
	/** @var array */
	protected $sizes;

	public function __construct(array $sizes) {
		$this->sizes = $sizes;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('size', ChoiceType::class, ['label' => 'Velikost', 'choices' => array_flip($this->sizes)]);
		$builder->add('quantity', NumberType::class, ['label' => 'Množství']);
		$builder->add('submit', SubmitType::class, ['label' => 'Přidat do košíku']);
	}
}
