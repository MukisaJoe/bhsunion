<?php
/**
 * Password Hash Generator for Admin User
 * 
 * Usage:
 * 1. Upload this file to your InfinityFree hosting (public_html or api folder)
 * 2. Access via browser: https://yourdomain.infinityfreeapp.com/generate_admin_hash.php
 * 3. Enter your desired password and click "Generate"
 * 4. Copy the generated hash
 * 5. Use it in the SQL INSERT statement to create admin user
 * 6. DELETE this file after use for security!
 */

$hash = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a5f;
            margin-top: 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #856404;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background: #1e3a5f;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #152d47;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        .hash {
            font-family: monospace;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
            margin-top: 10px;
        }
        .sql {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }
        code {
            font-family: monospace;
            font-size: 14px;
            display: block;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Password Hash Generator</h1>
        
        <div class="warning">
            ⚠️ <strong>Security Warning:</strong> Delete this file immediately after generating your hash!
        </div>

        <form method="POST">
            <label for="password">Enter Admin Password:</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Enter password for admin user"
                value="<?php echo htmlspecialchars($password); ?>"
                required
            >
            <button type="submit">Generate Hash</button>
        </form>

        <?php if ($hash): ?>
            <div class="result">
                <h3>✅ Hash Generated Successfully!</h3>
                <strong>Copy this hash:</strong>
                <div class="hash"><?php echo htmlspecialchars($hash); ?></div>
            </div>

            <div class="sql">
                <h3>📝 SQL INSERT Statement:</h3>
                <p>Use this SQL in phpMyAdmin to create your admin user:</p>
                <code>INSERT INTO users (email, password_hash, name, role, status, created_at, updated_at)
VALUES (
  'admin@bhs.local',
  '<?php echo htmlspecialchars($hash); ?>',
  'Admin',
  'admin',
  'active',
  NOW(),
  NOW()
);</code>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
            <strong>Steps to create admin:</strong>
            <ol>
                <li>Generate hash above</li>
                <li>Open phpMyAdmin in InfinityFree cPanel</li>
                <li>Select your database</li>
                <li>Click "SQL" tab</li>
                <li>Paste the SQL statement above</li>
                <li>Click "Go"</li>
                <li>Delete this file for security!</li>
            </ol>
        </div>
    </div>
</body>
</html>

