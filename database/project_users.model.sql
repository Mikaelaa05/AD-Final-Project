CREATE TABLE IF NOT EXISTS public."project_users" (
    project_id uuid NOT NULL REFERENCES projects (id),
    user_id uuid NOT NULL REFERENCES users (id),
    PRIMARY KEY (project_id, user_id)
);