CREATE TABLE IF NOT EXISTS public."projects" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(225) NOT NULL,
    description text,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
);