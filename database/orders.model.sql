-- orders.model.sql
-- Orders Database Model
-- Stores customer orders and purchase history
CREATE TABLE IF NOT EXISTS public."orders" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid NOT NULL,
    order_number varchar(50) UNIQUE NOT NULL,
    status varchar(20) DEFAULT 'pending',
    total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
    subtotal decimal(10,2) NOT NULL DEFAULT 0.00,
    tax_amount decimal(10,2) DEFAULT 0.00,
    shipping_amount decimal(10,2) DEFAULT 0.00,
    discount_amount decimal(10,2) DEFAULT 0.00,
    payment_method varchar(50),
    payment_status varchar(20) DEFAULT 'pending',
    shipping_address text,
    billing_address text,
    notes text,
    order_date timestamp DEFAULT CURRENT_TIMESTAMP,
    shipped_date timestamp,
    delivered_date timestamp,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Create order_items table for order details
CREATE TABLE IF NOT EXISTS public."order_items" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL DEFAULT 1,
    unit_price decimal(10,2) NOT NULL,
    total_price decimal(10,2) NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Create foreign key constraints
ALTER TABLE orders 
ADD CONSTRAINT fk_orders_customer_id 
FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_order_id 
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE;

ALTER TABLE order_items 
ADD CONSTRAINT fk_order_items_product_id 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_orders_customer_id ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_order_date ON orders(order_date);
CREATE INDEX IF NOT EXISTS idx_orders_order_number ON orders(order_number);
CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status);

CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);

-- Create trigger for updated_at
CREATE OR REPLACE FUNCTION update_orders_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_orders_updated_at
    BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_orders_updated_at_column();

-- Create function to generate order numbers
CREATE OR REPLACE FUNCTION generate_order_number()
RETURNS varchar(50) AS $$
DECLARE
    order_count integer;
    order_number varchar(50);
BEGIN
    SELECT COUNT(*) INTO order_count FROM orders;
    order_number := 'ORD-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD') || '-' || LPAD((order_count + 1)::text, 4, '0');
    RETURN order_number;
END;
$$ LANGUAGE plpgsql;