<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanLimit extends Model
{
    protected $fillable = [
        'plan_type',
        'max_products',
        'max_categories',
        'allow_custom_domain',
        'allow_checkout_pix',
        'allow_checkout_credit_card',
        'allow_analytics',
        'max_staff_accounts',
        'max_orders_per_month',
    ];

    protected $casts = [
        'max_products' => 'integer',
        'max_categories' => 'integer',
        'allow_custom_domain' => 'boolean',
        'allow_checkout_pix' => 'boolean',
        'allow_checkout_credit_card' => 'boolean',
        'allow_analytics' => 'boolean',
        'max_staff_accounts' => 'integer',
        'max_orders_per_month' => 'integer',
    ];

    public function scopeByPlanType($query, string $type)
    {
        return $query->where('plan_type', $type);
    }

    public function canAddMoreProducts(int $currentCount): bool
    {
        if ($this->max_products === 0) {
            return true;
        }

        return $currentCount < $this->max_products;
    }

    public function canAddMoreCategories(?int $currentCount): bool
    {
        if ($this->max_categories === null) {
            return true;
        }

        return $currentCount < $this->max_categories;
    }
}
