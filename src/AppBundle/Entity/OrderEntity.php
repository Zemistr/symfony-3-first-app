<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_order")
 */
class OrderEntity implements IEntity {
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/** @ORM\Column(type="string") */
	public $name;

	/**
	 * @ORM\Column(type="string")
	 * @Constraints\Email()
	 */
	public $email;

	/** @ORM\Column(type="text", nullable=true) */
	public $note;

	/** @ORM\Column(type="object") */
	public $cart = [];

	/** @ORM\Column(type="integer") */
	public $status = 0;

	/** @return integer */
	public function getId() {
		return $this->id;
	}
}
