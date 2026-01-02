-- SQL Command to Create Admin User: James Smith
-- Password: yc3480kj
-- 
-- Copy and paste this entire command into phpMyAdmin SQL tab

INSERT INTO users (
    email, 
    password_hash, 
    name, 
    role, 
    status, 
    created_at, 
    updated_at
) VALUES (
    'james.smith@bhs.local',
    '$2y$10$tQNq5u7hFPdy6fosTsZaduCnC1/Tj21M.Si5g03p4B6OQqQamiGtq',
    'James Smith',
    'admin',
    'active',
    NOW(),
    NOW()
);
