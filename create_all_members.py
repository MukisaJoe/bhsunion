#!/usr/bin/env python3
"""
Script to read names from Excel file and create SQL INSERT statements
for new members (excluding already existing ones)
"""

import pandas as pd
import subprocess
import os
import sys

# Password hash for 'Bhs2016'
PASSWORD_HASH = '$2y$10$oHRYalSVroVS60q9C..rC.5RvfZWoUP5eMySduKGaqMhKip0XCaoa'

def to_sentence_case(name):
    """Convert name to sentence case (Firstname Lastname)"""
    return ' '.join(word.capitalize() for word in str(name).lower().split())

def generate_email(name):
    """Generate email from name"""
    parts = name.lower().split()
    if len(parts) >= 2:
        return f"{parts[0]}.{parts[1]}@bhs.local"
    return name.lower().replace(' ', '.') + '@bhs.local'

def escape_sql_string(text):
    """Escape single quotes for SQL"""
    return str(text).replace("'", "''")

def get_existing_members():
    """Get list of existing member names from database"""
    env = os.environ.copy()
    env['PGPASSWORD'] = 'lLfPdFWBfBw3Y4nBARjroCVJRpecIVH3'
    
    result = subprocess.run(
        ["psql", "-h", "dpg-d5bn5ppr0fns7393b8lg-a.oregon-postgres.render.com", 
         "-U", "bhsunion", "-d", "bhsunion", "-t", "-A", "-c", 
         "SELECT UPPER(name) FROM users WHERE role = 'member';"],
        env=env,
        capture_output=True,
        text=True
    )
    
    if result.returncode != 0:
        print(f"Warning: Could not fetch existing members: {result.stderr}", file=sys.stderr)
        return set()
    
    existing = set()
    for line in result.stdout.split('\n'):
        name = line.strip()
        if name:
            existing.add(name.upper())
    
    return existing

def main():
    excel_file = '/home/s9/Bhs/Book1.xlsx'
    output_file = '/home/s9/Bhs/Hosting/create_remaining_members.sql'
    
    # Read Excel file
    try:
        df = pd.read_excel(excel_file)
    except Exception as e:
        print(f"Error reading Excel file: {e}", file=sys.stderr)
        sys.exit(1)
    
    # Get all names from first column
    all_names = df.iloc[:, 0].astype(str).str.strip().dropna().unique().tolist()
    all_names = [name for name in all_names if name and name != 'nan' and name.upper() != 'NAN']
    
    # Convert to sentence case
    all_names_formatted = [to_sentence_case(name) for name in all_names]
    
    # Get existing members
    existing_names_upper = get_existing_members()
    
    # Find new names
    new_names = []
    for name in all_names_formatted:
        if name.upper() not in existing_names_upper:
            new_names.append(name)
    
    print(f"Total names in Excel: {len(all_names_formatted)}")
    print(f"Existing members: {len(existing_names_upper)}")
    print(f"New members to add: {len(new_names)}")
    
    if not new_names:
        print("No new members to add!")
        return
    
    # Generate SQL
    sql_values = []
    for name in new_names:
        email = generate_email(name)
        escaped_name = escape_sql_string(name)
        sql_values.append(
            f"('{email}', '{PASSWORD_HASH}', '{escaped_name}', 'member', 'active', NOW(), NOW())"
        )
    
    # Write SQL file
    with open(output_file, 'w') as f:
        f.write("-- Create remaining members from Excel file\n")
        f.write("-- Names converted to Sentence Case (Firstname Lastname)\n")
        f.write(f"-- Default password: Bhs2016\n")
        f.write(f"-- Total new members: {len(new_names)}\n\n")
        f.write("INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at) VALUES\n")
        f.write(",\n".join(sql_values))
        f.write(";\n")
    
    print(f"\n✅ SQL file created: {output_file}")
    print(f"   Contains {len(new_names)} new member accounts")

if __name__ == '__main__':
    main()

