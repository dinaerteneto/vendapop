<?php

return [
    'cache_ttl' => (int) env('PLAN_LIMITS_CACHE_TTL', 3600),
    'default_limits' => [
        'free' => [
            'max_products' => 6,
            'max_categories' => 3,
            'allow_checkout_pix' => false,
            'max_orders_per_month' => 10,
        ],
        'basic' => [
            'max_products' => 30,
            'max_categories' => null,
            'allow_checkout_pix' => true,
            'max_orders_per_month' => null,
        ],
        'professional' => [
            'max_products' => 100,
            'max_categories' => null,
            'allow_checkout_pix' => true,
            'allow_checkout_credit_card' => true,
            'allow_analytics' => true,
        ],
        'premium' => [
            'max_products' => 0,
            'max_categories' => null,
            'allow_custom_domain' => true,
            'allow_checkout_pix' => true,
            'allow_checkout_credit_card' => true,
            'allow_analytics' => true,
        ],
    ],
];
