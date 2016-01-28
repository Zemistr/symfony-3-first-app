<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderFormType extends AbstractType {
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', TextType::class, ['label' => 'Jméno a příjmení']);
		$builder->add('email', EmailType::class, ['label' => 'Email']);
		$builder->add('note', TextareaType::class, ['label' => 'Poznámka k objednávce', 'required' => false]);
		$builder->add('submit', SubmitType::class, ['label' => 'Odeslat objednávku']);
	}
}
