CREATE TABLE IF NOT EXISTS public."tasks" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id uuid NOT NULL REFERENCES projects (id),
    assigned_to uuid REFERENCES users (id),
    title varchar(225) NOT NULL,
    description text,
    status varchar(50) DEFAULT 'pending',
    due_date date
);