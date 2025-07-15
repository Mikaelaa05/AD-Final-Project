CREATE TABLE IF NOT EXISTS public."users" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    first_name varchar(225) NOT NULL,
    middle_name varchar(225),
    last_name varchar(225) NOT NULL,
    password varchar(225) NOT NULL,
    username varchar(225) NOT NULL,
    email varchar(255) UNIQUE NOT NULL,
    phone varchar(20),
    role varchar(225) NOT NULL,
    is_active boolean DEFAULT true
);