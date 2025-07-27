-- products.model.sql
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
    image_url varchar(500),                    -- Add this for web images
    image_alt_text varchar(255),               -- Add this for accessibility
    image_caption varchar(255),                -- Add this for image descriptions
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Create index for active products with images
CREATE INDEX IF NOT EXISTS idx_products_active_with_images ON products(is_active, image_url) WHERE image_url IS NOT NULL;

-- Create index for category and active status
CREATE INDEX IF NOT EXISTS idx_products_category_active ON products(category, is_active);

-- Create trigger for updated_at
CREATE OR REPLACE FUNCTION update_products_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_products_updated_at 
    BEFORE UPDATE ON products 
    FOR EACH ROW EXECUTE FUNCTION update_products_updated_at_column();