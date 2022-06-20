<?php
namespace App\Support\Cost;

use App\Services\Convert\convertEnglishToPersian;
use App\Support\Basket\Basket;
use App\Support\Cost\Contracts\CostInterface;

class BasketCost implements CostInterface
{
    public $basket;
    public function __construct(Basket $basket)
    {
        $this->basket = $basket;
        // dd()
    }
    public function getCost()
    {
        return $this->basket->subTotal();
    }
    public function getTotalCosts()
    {
        return $this->getCost();
    }
    public function persianDescription()
    {
        return ' سبد خرید ';
    }
    public function getSummary()
    {
        return [$this->persianDescription() => convertEnglishToPersian::convertEnglishToPersian((int)$this->getCost())];
    }
}