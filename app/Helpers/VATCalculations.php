<?php

namespace App\Helpers;

use App\Models\Retail;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class VATCalculations implements CastsAttributes
{
    protected $VAT;
    public function getVAT()
    {

        $VAT = 16;
        $retail = auth()->user()->sessionRetail->retail;
        if ($retail->VAT) {
            $VAT = $retail->VAT;
        }
        return $this->VAT = $VAT;
    }

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {

       $this->VAT = $this->getVAT();
        $pricing = array("VAT" => $this->VAT+"%",
            "initial_price" => $value,
            "after_tax" => $value + (($this->VAT / 100) * $value),
        );
        return $pricing;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
