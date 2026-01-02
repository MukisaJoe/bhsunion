-- SQL Command to Create Member User: Sarah Juliet
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
    'sarah.juliet@bhs.local',
    '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa',
    'Sarah Juliet',
    'member',
    'active',
    NOW(),
    NOW()
);

