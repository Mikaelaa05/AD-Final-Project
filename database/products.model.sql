CREATE TABLE IF NOT EXISTS public."products" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    category varchar(100) NOT NULL,
    price decimal(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity integer NOT NULL DEFAULT 0,
    sku varchar(100) UNIQUE NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
