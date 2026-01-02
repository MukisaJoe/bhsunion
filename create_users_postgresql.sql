-- Create Admin User: James Smith
INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at)
VALUES (
    'james.smith@bhs.local',
    '$2y$10$tQNq5u7hFPdy6fosTsZaduCnC1/Tj21M.Si5g03p4B6OQqQamiGtq',
    'James Smith',
    'admin',
    'active',
    NOW(),
    NOW()
);

-- Create Member User: Sarah Juliet
INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at)
VALUES (
    'sarah.juliet@bhs.local',
    '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa',
    'Sarah Juliet',
    'member',
    'active',
    NOW(),
    NOW()
);

