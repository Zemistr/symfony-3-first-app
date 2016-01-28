<?php
namespace AppBundle\Entity;

class CartItemEntity implements IEntity {
	protected $sku;
	protected $title;
	protected $price;
	public $size;
	public $quantity = 1;

	public function __construct($sku, $title, $price) {
		$this->sku = $sku;
		$this->title = $title;
		$this->price = $price;
	}

	public function getSku() {
		return $this->sku;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getPrice() {
		return $this->price;
	}
}
