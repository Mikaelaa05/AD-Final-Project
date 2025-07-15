CREATE TABLE IF NOT EXISTS public."products" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    price decimal(10,2) NOT NULL,
    category varchar(100) NOT NULL,
    stock_quantity integer DEFAULT 0,
    sku varchar(50) UNIQUE NOT NULL,
    status varchar(20) DEFAULT 'active',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
