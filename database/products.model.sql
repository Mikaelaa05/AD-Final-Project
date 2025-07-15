CREATE TABLE IF NOT EXISTS public."products" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    category varchar(100),
    price decimal(10,2) NOT NULL,
    cost decimal(10,2),
    sku varchar(50) UNIQUE NOT NULL,
    stock_quantity integer DEFAULT 0,
    weight decimal(8,2),
    is_active boolean DEFAULT true,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
