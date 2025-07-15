CREATE TABLE IF NOT EXISTS public."users" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    username varchar(100) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    first_name varchar(150) NOT NULL,
    middle_name varchar(150),
    last_name varchar(150) NOT NULL,
    corporate_id varchar(50) UNIQUE NOT NULL,
    role varchar(100) NOT NULL,
    department varchar(100) NOT NULL,
    security_clearance varchar(50) DEFAULT 'basic',
    neural_link_id varchar(100) UNIQUE,
    augmentation_level integer DEFAULT 0,
    contact_frequency varchar(20) DEFAULT 'standard',
    status varchar(20) DEFAULT 'active',
    hire_date date DEFAULT CURRENT_DATE,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);