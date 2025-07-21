CREATE TABLE IF NOT EXISTS public."customers" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    username varchar(255) UNIQUE NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    phone varchar(20),
    address text,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster email lookups (useful for customer login)
CREATE INDEX IF NOT EXISTS idx_customers_email ON customers(email);

-- Create index for username lookups (for login)
CREATE INDEX IF NOT EXISTS idx_customers_username ON customers(username);

-- Create index for name searches
CREATE INDEX IF NOT EXISTS idx_customers_name ON customers(name);