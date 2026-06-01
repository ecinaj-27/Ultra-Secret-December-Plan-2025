<?php
// Database setup script
// This script will create the database and tables if they don't exist

require_once 'config/database.php';

$errors = [];
$success = [];
$step = isset($_GET['step']) ? $_GET['step'] : 'check';

// Read the SQL file
$sql_file = __DIR__ . '/database.sql';
if (!file_exists($sql_file)) {
    $errors[] = "database.sql file not found!";
    $step = 'error';
}

if ($step === 'install' && file_exists($sql_file)) {
    try {
        // Connect without database first
        $temp_conn = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $temp_conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Database '" . DB_NAME . "' created or already exists.";
        
        // Connect to the database
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Read and execute SQL file
        $sql = file_get_contents($sql_file);
        
        // Remove CREATE DATABASE and USE statements since we already have the database
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/i', '', $sql);
        $sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $conn->exec($statement);
                } catch (PDOException $e) {
                    // Ignore errors for existing tables/objects
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $errors[] = "SQL Error: " . $e->getMessage() . " (Statement: " . substr($statement, 0, 50) . "...)";
                    }
                }
            }
        }
        
        $success[] = "Database tables created successfully!";
        $step = 'complete';
        
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
        $step = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Our Secret Place</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .info-box h3 {
            color: #004085;
            margin-bottom: 10px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #004085;
        }
        .info-box li {
            margin-bottom: 5px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-block {
            display: block;
            text-align: center;
            width: 100%;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .text-center {
            text-align: center;
        }
        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Setup</h1>
        <p class="subtitle">Setup your database for Our Secret Place</p>
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <?php foreach ($success as $msg): ?>
                <div class="alert alert-success">
                    <strong>Success:</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($step === 'check' || $step === 'error'): ?>
            <div class="info-box">
                <h3>Before you begin:</h3>
                <ul>
                    <li>Make sure MySQL/MariaDB is running in XAMPP</li>
                    <li>Default settings use: <code>root</code> user with no password</li>
                    <li>This script will create the database and all required tables</li>
                </ul>
            </div>
            
            <form method="GET" action="setup.php">
                <input type="hidden" name="step" value="install">
                <button type="submit" class="btn btn-block">
                    🚀 Install Database
                </button>
            </form>
        <?php elseif ($step === 'complete'): ?>
            <div class="info-box">
                <h3>✅ Setup Complete!</h3>
                <p>Your database has been successfully set up. You can now:</p>
                <ul>
                    <li>Register a new account</li>
                    <li>Start using the website</li>
                </ul>
            </div>
            
            <div class="text-center mt-20">
                <a href="register.php" class="btn btn-success">Go to Registration</a>
                <a href="index.php" class="btn mt-20">Go to Home</a>
            </div>
            
            <p class="text-center mt-20" style="color: #666; font-size: 14px;">
                <strong>Note:</strong> For security, you should delete or protect this setup.php file after installation.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>







