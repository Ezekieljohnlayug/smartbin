<?php
session_start();

// Debug line to check session contents
error_log("Session contents: " . print_r($_SESSION, true));

if (!isset($_SESSION["user"])) {
    header("Location: login.php?redirect=download");
    exit();
}

require_once "database.php";

// Create app_downloads table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS app_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    download_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('completed', 'failed') DEFAULT 'completed',
    FOREIGN KEY (user_id) REFERENCES user(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Create purchases table if it doesn't exist - Remove amount field
$sql = "CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    product_name VARCHAR(255) NOT NULL,
    status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    user_email VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating purchases table: " . $conn->error);
}

// Get user ID from session or database
if (!isset($_SESSION["user_id"])) {
    // Check what format the user session data is in
    if (isset($_SESSION["user"])) {
        if (is_array($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
            // If user is an array with email key
            $email = $_SESSION["user"]["email"];
        } elseif (is_string($_SESSION["user"])) {
            // If user is just a string containing the email
            $email = $_SESSION["user"];
        } else {
            die("User session data is in an unexpected format. Please log in again.");
        }
        
        // Now get the user ID from the database using the email
        $sql = "SELECT id FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            $_SESSION["user_id"] = $user["id"];
        } else {
            die("User not found in database");
        }
    } else {
        die("User information not found in session. Please log in again.");
    }
}

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Get user email from session - handle both possible formats
if (is_array($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $user_email = $_SESSION["user"]["email"];
} elseif (is_string($_SESSION["user"])) {
    $user_email = $_SESSION["user"];
} else {
    // If we can't determine the email, get it from the database
    $sql = "SELECT email FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $user_email = $user["email"];
    } else {
        die("Could not retrieve user email. Please log in again.");
    }
}

if (isset($_POST['download'])) {
    // Check if user has already downloaded/purchased this app
    $sql = "SELECT id FROM purchases WHERE user_id = ? AND product_name = 'Smart Bin App'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Only create a new record if the user hasn't downloaded before
    if ($result->num_rows == 0) {
        // Record the download
        $sql = "INSERT INTO app_downloads (user_id) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION["download_success"] = true;
            
            // If this is a paid app, record the purchase
            if (isset($_POST['purchase']) && $_POST['purchase'] == 'true') {
                $product_name = "Smart Bin App";
                
                $sql = "INSERT INTO purchases (user_id, product_name, user_email) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $user_id, $product_name, $user_email);
                
                if (!$stmt->execute()) {
                    error_log("Error recording purchase: " . $conn->error);
                }
            }
        } else {
            $error = "Error recording download: " . $conn->error;
        }
    }
    
    // Always allow the download to proceed
    $file_path = 'downloads/setup.exe'; // Path to your setup file
    
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="SmartBinApp-Setup.exe"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        die("Setup file not found. Please contact support.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Bin App - Download</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .download-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .app-info {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .app-info:hover {
            transform: translateY(-5px);
        }
        .app-image {
            max-width: 200px;
            margin: 20px auto;
            display: block;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-list li {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list li i {
            color: #28a745;
            margin-right: 15px;
            font-size: 1.2rem;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 50%;
            flex-shrink: 0;
        }
        .requirements {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        .requirements p {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 0;
        }
        .requirements p:last-child {
            margin-bottom: 0;
        }
        .requirements p i {
            color: #28a745;
            margin-right: 15px;
            font-size: 1.2rem;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 50%;
            flex-shrink: 0;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
            text-align: center;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #28a745;
        }
        .download-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin: 20px 0;
            transition: all 0.3s ease;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            border: none;
            cursor: pointer;
        }
        .download-btn:hover {
            background: linear-gradient(45deg, #218838, #1ba87e);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="download-container">
        <div class="app-info">
            <h1 class="text-center mb-4">Smart Bin App</h1>
            <img src="images/app-icon.png" alt="Smart Bin App" class="app-image">
            
            <div class="row mt-5">
                <div class="col-md-6">
                    <h3 class="section-title">Key Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-binoculars"></i> Real-time bin monitoring</li>
                        <li><i class="fas fa-gift"></i> Reward points system</li>
                        <li><i class="fas fa-recycle"></i> Waste sorting guidance</li>
                        <li><i class="fas fa-leaf"></i> Environmental impact tracking</li>
                        <li><i class="fas fa-users"></i> Community challenges</li>
                        <li><i class="fas fa-wifi-slash"></i> Offline functionality</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3 class="section-title">System Requirements</h3>
                    <div class="requirements">
                        <p><i class="fas fa-mobile-alt"></i> <strong>Operating System:</strong> Android 8.0 or higher</p>
                        <p><i class="fas fa-hdd"></i> <strong>Storage:</strong> 50MB available space</p>
                        <p><i class="fas fa-wifi"></i> <strong>Internet:</strong> Required for full functionality</p>
                        <p><i class="fas fa-bluetooth"></i> <strong>Bluetooth:</strong> Required for bin connection</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <form method="post">
                    <input type="hidden" name="purchase" value="true">
                    <button type="submit" name="download" class="download-btn">
                        <i class="fas fa-download me-2"></i>Download Now
                    </button>
                </form>
                <p class="text-muted mt-2">Version 1.0.0 | Last updated: March 2024</p>
            </div>
        </div>
    </div>
</body>
</html>