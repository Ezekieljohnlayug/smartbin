<?php
require_once "database.php";

// Create app_downloads table
$sql = "CREATE TABLE IF NOT EXISTS app_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    download_date DATETIME NOT NULL,
    status ENUM('completed', 'failed') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table app_downloads created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?> 