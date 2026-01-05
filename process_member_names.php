<?php
/**
 * Helper script to convert member names from ALL CAPS to Sentence Case
 * and generate SQL INSERT statements
 * 
 * Usage: php process_member_names.php < input.txt > output.sql
 * 
 * Input format: One name per line in ALL CAPS format like:
 * AGASHA BRENDA
 * APOFIA AKUDIN
 */

// Password hash for 'Bhs2016'
$passwordHash = '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa';

// Function to convert name to sentence case
function toSentenceCase($name) {
    // Convert to lowercase first, then capitalize first letter of each word
    $words = explode(' ', strtolower(trim($name)));
    $formatted = array_map('ucfirst', $words);
    return implode(' ', $formatted);
}

// Function to generate email from name
function generateEmail($name) {
    $parts = explode(' ', strtolower(trim($name)));
    if (count($parts) >= 2) {
        return $parts[0] . '.' . $parts[1] . '@bhs.local';
    }
    return strtolower(str_replace(' ', '.', $name)) . '@bhs.local';
}

echo "-- Create members from list\n";
echo "-- Names converted to Sentence Case (Firstname Lastname)\n";
echo "-- Default password: Bhs2016\n\n";

echo "INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at) VALUES\n";

$lines = file('php://stdin', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$values = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    $sentenceCase = toSentenceCase($line);
    $email = generateEmail($sentenceCase);
    
    $values[] = sprintf(
        "('%s', '%s', '%s', 'member', 'active', NOW(), NOW())",
        $email,
        $passwordHash,
        pg_escape_string($sentenceCase)
    );
}

echo implode(",\n", $values) . ";\n";

