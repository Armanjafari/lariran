<?php
namespace App\Support\Cost;

use App\Models\Shiping;
use App\Support\Cost\Contracts\CostInterface;
use GhaniniaIR\Shipping\Shipping;
use Illuminate\Http\Request;

class ShippingCost implements CostInterface
{
    private $SHIPPING_COST = 0;
    private $weight;
    private $price;
    private $request;
    private $cost;
    public function __construct(CostInterface $cost , Request $request)
    {
        $this->cost = $cost;
        $this->request = $request;
        $this->setVariables();
    }
    public function setVariables()
    {
        foreach ($this->cost->basket->all() as $full) {
            $this->weight += $full->product->weight;
            $this->price += ($full->price * $full->currency->value) * 10;
        }
        
    }
    public function getCost()
    {
        if (!$this->request->has('shipping')) {
            return 0;
        }
        $dst = Shiping::find($this->request->shipping)->city->province->id;
        return (int)(Shipping::pishtaz(5,$dst,$this->weight,$this->price)->getPrice() / 10);
    }
    public function getTotalCosts()
    {
        return $this->cost->getTotalCosts() + $this->getCost();
    }
    public function persianDescription()
    {
        return ' هزینه حمل و نقل ';
    }
    public function getSummary()
    {
        return array_merge($this->cost->getSummary() , [$this->persianDescription() => $this->getCost()]);
    }
}