<?php
/**
 * Orders Static Data
 * Sample customer orders for testing and development
 */
return [
    [
        'customer_email' => 'john.customer@email.com',
        'status' => 'completed',
        'total_amount' => 2150.00,
        'subtotal' => 2000.00,
        'tax_amount' => 120.00,
        'shipping_amount' => 30.00,
        'discount_amount' => 0.00,
        'payment_method' => 'credit_card',
        'payment_status' => 'paid',
        'shipping_address' => '123 Customer Street, Binondo, Manila 1006',
        'billing_address' => '123 Customer Street, Binondo, Manila 1006',
        'notes' => 'Please handle with care - contains cybernetic implants',
        'items' => [
            ['product_sku' => 'CYBEYE-001', 'quantity' => 1, 'unit_price' => 1500.00],
            ['product_sku' => 'NEURALCHIP-001', 'quantity' => 1, 'unit_price' => 500.00]
        ]
    ],
    [
        'customer_email' => 'sarah.williams@email.com',
        'status' => 'processing',
        'total_amount' => 3200.00,
        'subtotal' => 3000.00,
        'tax_amount' => 180.00,
        'shipping_amount' => 20.00,
        'discount_amount' => 0.00,
        'payment_method' => 'paypal',
        'payment_status' => 'paid',
        'shipping_address' => '456 Website Ave, Diliman, Quezon City 1101',
        'billing_address' => '456 Website Ave, Diliman, Quezon City 1101',
        'notes' => 'Express delivery requested',
        'items' => [
            ['product_sku' => 'CYBERARM-001', 'quantity' => 1, 'unit_price' => 3000.00]
        ]
    ],
    [
        'customer_email' => 'michael.johnson@email.com',
        'status' => 'shipped',
        'total_amount' => 850.00,
        'subtotal' => 800.00,
        'tax_amount' => 48.00,
        'shipping_amount' => 2.00,
        'discount_amount' => 0.00,
        'payment_method' => 'bank_transfer',
        'payment_status' => 'paid',
        'shipping_address' => '789 Signup Blvd, Salcedo Village, Makati 1227',
        'billing_address' => '789 Signup Blvd, Salcedo Village, Makati 1227',
        'notes' => 'Leave at security desk if not home',
        'items' => [
            ['product_sku' => 'SMARTGLASS-001', 'quantity' => 2, 'unit_price' => 250.00],
            ['product_sku' => 'NEUROPLUG-001', 'quantity' => 1, 'unit_price' => 300.00]
        ]
    ],
    [
        'customer_email' => 'emma.davis@email.com',
        'status' => 'pending',
        'total_amount' => 1200.00,
        'subtotal' => 1150.00,
        'tax_amount' => 69.00,
        'shipping_amount' => -19.00,
        'discount_amount' => 0.00,
        'payment_method' => 'cash_on_delivery',
        'payment_status' => 'pending',
        'shipping_address' => '321 Register St, Kapitolyo, Pasig 1603',
        'billing_address' => '321 Register St, Kapitolyo, Pasig 1603',
        'notes' => 'COD order - verify ID upon delivery',
        'items' => [
            ['product_sku' => 'HOLOWATCH-001', 'quantity' => 1, 'unit_price' => 400.00],
            ['product_sku' => 'CYBERDECK-001', 'quantity' => 1, 'unit_price' => 750.00]
        ]
    ],
    [
        'customer_email' => 'maria.santos@email.com',
        'status' => 'delivered',
        'total_amount' => 1850.00,
        'subtotal' => 1750.00,
        'tax_amount' => 105.00,
        'shipping_amount' => -5.00,
        'discount_amount' => 0.00,
        'payment_method' => 'gcash',
        'payment_status' => 'paid',
        'shipping_address' => '987 Commerce St, Lahug, Cebu City 6000',
        'billing_address' => '987 Commerce St, Lahug, Cebu City 6000',
        'notes' => 'Customer very satisfied - left 5-star review',
        'items' => [
            ['product_sku' => 'NEURALCHIP-001', 'quantity' => 2, 'unit_price' => 500.00],
            ['product_sku' => 'SMARTGLASS-001', 'quantity' => 3, 'unit_price' => 250.00]
        ]
    ],
    [
        'customer_email' => 'carlos.reyes@email.com',
        'status' => 'cancelled',
        'total_amount' => 0.00,
        'subtotal' => 2500.00,
        'tax_amount' => 0.00,
        'shipping_amount' => 0.00,
        'discount_amount' => 0.00,
        'payment_method' => 'credit_card',
        'payment_status' => 'refunded',
        'shipping_address' => '159 Tech Hub, IT Park, Cebu City 6000',
        'billing_address' => '159 Tech Hub, IT Park, Cebu City 6000',
        'notes' => 'Customer cancelled - item out of stock, full refund processed',
        'items' => [
            ['product_sku' => 'CYBERARM-001', 'quantity' => 1, 'unit_price' => 2500.00]
        ]
    ]
];