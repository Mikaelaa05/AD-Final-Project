CREATE TABLE IF NOT EXISTS public."couriers" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    callsign varchar(100) UNIQUE NOT NULL,
    contact_info varchar(255) NOT NULL,
    vehicle_type varchar(100) NOT NULL,
    specialization varchar(150),
    status varchar(20) DEFAULT 'available',
    rating decimal(3,2) DEFAULT 5.00,
    delivery_zones text[],
    security_clearance varchar(50) DEFAULT 'standard',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
