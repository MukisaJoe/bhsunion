-- Create new members from the provided list
-- Names converted to Sentence Case (Firstname Lastname)
-- Default password: Bhs2016 (will be hashed in PHP when members first login)

-- Insert members with proper sentence case formatting
INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at) VALUES
-- Password hash for 'Bhs2016' is $2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa
('agasha.brenda@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Agasha Brenda', 'member', 'active', NOW(), NOW()),
('apofia.akudin@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Apofia Akudin', 'member', 'active', NOW(), NOW()),
('arinaitwe.louis@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Arinaitwe Louis', 'member', 'active', NOW(), NOW()),
('athieno.hope@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Athieno Hope', 'member', 'active', NOW(), NOW()),
('athuaire.comfort@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Athuaire Comfort', 'member', 'active', NOW(), NOW()),
('adior.timothy@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Adior Timothy', 'member', 'active', NOW(), NOW()),
('birungi.patricia@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Birungi Patricia', 'member', 'active', NOW(), NOW()),
('bugembe.andrew@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Bugembe Andrew', 'member', 'active', NOW(), NOW()),
('daina.nulutaaya@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Daina Nulutaaya', 'member', 'active', NOW(), NOW()),
('kamoga.pius@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Kamoga Pius', 'member', 'active', NOW(), NOW()),
('kyambadde.victor@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Kyambadde Victor', 'member', 'active', NOW(), NOW()),
('luzige.william@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Luzige William', 'member', 'active', NOW(), NOW()),
('mulinda.douglas@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Mulinda Douglas', 'member', 'active', NOW(), NOW()),
('nakalembe.bobo@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Nakalembe Bobo', 'member', 'active', NOW(), NOW()),
('naluwooza.angella@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Naluwooza Angella', 'member', 'active', NOW(), NOW()),
('nalwanana.edmond@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Nalwanana Edmond', 'member', 'active', NOW(), NOW()),
('navuma.vanessa@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Navuma Vanessa', 'member', 'active', NOW(), NOW()),
('porsha.becky@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Porsha Becky', 'member', 'active', NOW(), NOW()),
('ssentongo.john@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Ssentongo John', 'member', 'active', NOW(), NOW()),
('zaharah.namuswe@bhs.local', '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa', 'Zaharah Namuswe', 'member', 'active', NOW(), NOW());

