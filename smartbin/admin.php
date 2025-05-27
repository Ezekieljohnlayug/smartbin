<?php
session_start();
require_once "database.php";

// Check if admin tables exist, if not create them
function setupAdminTables($conn) {
    // Create admin table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME DEFAULT NULL
    )";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating admin table: " . $conn->error);
    }

    // Create app_versions table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS app_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(20) NOT NULL,
        release_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        notes TEXT
    )";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating app_versions table: " . $conn->error);
    }
    
    // Insert default version if table is empty
    $sql = "SELECT COUNT(*) as count FROM app_versions";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $sql = "INSERT INTO app_versions (version, notes) VALUES ('1.0.0', 'Initial release')";
        $conn->query($sql);
    }
}

// Call the setup function
setupAdminTables($conn);

// Handle admin registration
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $sql = "SELECT id FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $register_error = "Email already exists. Please use another email or login.";
    } else {
        $sql = "INSERT INTO admin (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            $register_success = "Registration successful. Please login.";
        } else {
            $register_error = "Registration failed: " . $conn->error;
        }
    }
}

// Handle admin login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT id, name, email, password FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            // Update last login time
            $sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $admin['id']);
            $stmt->execute();
            
            // Redirect to dashboard
            header("Location: admin.php?page=dashboard");
            exit();
        } else {
            $login_error = "Invalid password";
        }
    } else {
        $login_error = "Email not found";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: admin.php");
    exit();
}

// CRUD Operations for download logs
if (isset($_SESSION['admin_id'])) {
    // Handle version adding
    if (isset($_POST['add_version'])) {
        $version = $_POST['version'];
        $notes = $_POST['notes'];
        
        $sql = "INSERT INTO app_versions (version, notes) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $version, $notes);
        
        if ($stmt->execute()) {
            $version_success = "Version added successfully";
        } else {
            $version_error = "Error adding version: " . $conn->error;
        }
    }
    
    // Handle delete operation
    if (isset($_GET['delete']) && isset($_GET['id'])) {
        $id = $_GET['id'];
        $table = $_GET['delete'];
        
        if ($table === 'purchases') {
            $sql = "DELETE FROM purchases WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $delete_success = "Record deleted successfully";
            } else {
                $delete_error = "Error deleting record: " . $conn->error;
            }
        } elseif ($table === 'app_downloads') {
            $sql = "DELETE FROM app_downloads WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $delete_success = "Download record deleted successfully";
            } else {
                $delete_error = "Error deleting download record: " . $conn->error;
            }
        }
        
        // Redirect to prevent resubmission
        header("Location: admin.php?page=" . $_GET['return']);
        exit();
    }
    
    // Handle edit operation
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $user_email = $_POST['user_email'];
        $version = $_POST['version'];
        $status = $_POST['status'];
        
        $sql = "UPDATE purchases SET user_id = ?, user_email = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $user_id, $user_email, $status, $id);
        
        if ($stmt->execute()) {
            $update_success = "Record updated successfully";
        } else {
            $update_error = "Error updating record: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Bin App - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap\bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container, .register-container {
            display: flex; 
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #28a745;
            border-bottom: 2px solid #28a745;
        }
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table th {
            background: #f8f9fa;
            color: #495057;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin-right: 5px;
        }
        .admin-header {
            background: #343a40;
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        .admin-name {
            font-weight: 600;
        }
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card i {
            font-size: 2rem;
            color: #28a745;
            margin-bottom: 10px;
        }
        .stats-card .stats-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #343a40;
        }
        .stats-card .stats-label {
            color: #6c757d;
            font-weight: 500;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['admin_id'])) : ?>
        <!-- Admin Dashboard -->
        <div class="admin-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="admin-name me-3">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['admin_name']; ?>
                        </span>
                        <a href="admin.php?logout=1" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-container">
            <?php if (isset($update_success)) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $update_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($update_error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $update_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($delete_success)) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $delete_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($delete_error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $delete_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($version_success)) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $version_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($version_error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $version_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>" href="admin.php?page=dashboard">
                        <i class="fas fa-chart-line me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'downloads') ? 'active' : ''; ?>" href="admin.php?page=downloads">
                        <i class="fas fa-download me-1"></i> Downloads
                    </a>
                </li>
      
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'versions') ? 'active' : ''; ?>" href="admin.php?page=versions">
                        <i class="fas fa-code-branch me-1"></i> App Versions
                    </a>
                </li>
            </ul>
            
            <?php 
            // Get page from URL parameter
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            
            // Show the appropriate page content
            switch ($page) {
                case 'dashboard':
                    // Calculate statistics
                    $sql = "SELECT COUNT(*) as download_count FROM app_downloads";
                    $result = $conn->query($sql);
                    $download_count = $result->fetch_assoc()['download_count'];
                    
                    $sql = "SELECT COUNT(*) as purchase_count FROM purchases";
                    $result = $conn->query($sql);
                    $purchase_count = $result->fetch_assoc()['purchase_count'];
                    
                    $sql = "SELECT COUNT(DISTINCT user_id) as user_count FROM app_downloads";
                    $result = $conn->query($sql);
                    $user_count = $result->fetch_assoc()['user_count'];
                    
                    $sql = "SELECT COUNT(*) as version_count FROM app_versions";
                    $result = $conn->query($sql);
                    $version_count = $result->fetch_assoc()['version_count'];
                    
                    // Get recent downloads
                    $sql = "SELECT ad.id, ad.download_date, ad.status, u.email 
                            FROM app_downloads ad
                            JOIN user u ON ad.user_id = u.id
                            ORDER BY ad.download_date DESC LIMIT 5";
                    $recent_downloads = $conn->query($sql);
            ?>
                <!-- Dashboard Statistics -->
                <h3 class="mb-4">Overview</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-download"></i>
                            <div class="stats-value"><?php echo $download_count; ?></div>
                            <div class="stats-label">Total Downloads</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-shopping-cart"></i>
                            <div class="stats-value"><?php echo $purchase_count; ?></div>
                            <div class="stats-label">Total Purchases</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-users"></i>
                            <div class="stats-value"><?php echo $user_count; ?></div>
                            <div class="stats-label">Unique Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-code-branch"></i>
                            <div class="stats-value"><?php echo $version_count; ?></div>
                            <div class="stats-label">App Versions</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Downloads -->
                <h3 class="mt-5 mb-3">Recent Downloads</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Email</th>
                                <th>Download Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_downloads->num_rows > 0): ?>
                                <?php while($row = $recent_downloads->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['download_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($row['status'] == 'completed') ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent downloads found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php
                    break;
                case 'downloads':
                    // Get all downloads with user information
                    $sql = "SELECT ad.id, ad.user_id, u.email, ad.download_date, ad.status, 
                            (SELECT version FROM app_versions ORDER BY release_date DESC LIMIT 1) as version 
                            FROM app_downloads ad
                            JOIN user u ON ad.user_id = u.id
                            ORDER BY ad.download_date DESC";
                    $downloads = $conn->query($sql);
            ?>
                <!-- Downloads Table -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>App Downloads</h3>
                    <div>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-file-export me-1"></i> Export Data
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Email</th>
                                <th>Version</th>
                                <th>Download Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($downloads->num_rows > 0): ?>
                                <?php while($row = $downloads->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['version']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['download_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($row['status'] == 'completed') ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary btn-action view-download" data-id="<?php echo $row['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="admin.php?page=downloads&delete=app_downloads&id=<?php echo $row['id']; ?>&return=downloads" 
                                               class="btn btn-sm btn-danger btn-action" 
                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No downloads found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php
                    break;
                case 'purchases':
                    // Get all purchases with user information
                    $sql = "SELECT p.id, p.user_id, p.user_email, p.purchase_date, p.product_name, p.status,
                            (SELECT version FROM app_versions ORDER BY release_date DESC LIMIT 1) as version
                            FROM purchases p
                            ORDER BY p.purchase_date DESC";
                    $purchases = $conn->query($sql);
            ?>
                <!-- Purchases Table -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>App Purchases</h3>
                    <div>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-file-export me-1"></i> Export Data
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Email</th>
                                <th>Product</th>
                                <th>Version</th>
                                <th>Purchase Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($purchases->num_rows > 0): ?>
                                <?php while($row = $purchases->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['user_email']; ?></td>
                                        <td><?php echo $row['product_name']; ?></td>
                                        <td><?php echo $row['version']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['purchase_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($row['status'] == 'completed') ? 'success' : (($row['status'] == 'pending') ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary btn-action edit-purchase" 
                                               data-id="<?php echo $row['id']; ?>"
                                               data-userid="<?php echo $row['user_id']; ?>"
                                               data-email="<?php echo $row['user_email']; ?>"
                                               data-version="<?php echo $row['version']; ?>"
                                               data-status="<?php echo $row['status']; ?>"
                                               data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="admin.php?page=purchases&delete=purchases&id=<?php echo $row['id']; ?>&return=purchases" 
                                               class="btn btn-sm btn-danger btn-action" 
                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No purchases found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Purchase Record</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post" action="admin.php?page=purchases">
                                    <input type="hidden" name="id" id="edit-id">
                                    
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">User ID</label>
                                        <input type="number" class="form-control" id="edit-userid" name="user_id" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="user_email" class="form-label">User Email</label>
                                        <input type="email" class="form-control" id="edit-email" name="user_email" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="version" class="form-label">Version</label>
                                        <input type="text" class="form-control" id="edit-version" name="version" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="edit-status" name="status" required>
                                            <option value="completed">Completed</option>
                                            <option value="pending">Pending</option>
                                            <option value="failed">Failed</option>
                                        </select>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                    break;
                case 'versions':
                    // Get all app versions
                    $sql = "SELECT * FROM app_versions ORDER BY release_date DESC";
                    $versions = $conn->query($sql);
            ?>
                <!-- App Versions Table -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>App Versions</h3>
                    <div>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVersionModal">
                            <i class="fas fa-plus me-1"></i> Add New Version
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Version</th>
                                <th>Release Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($versions->num_rows > 0): ?>
                                <?php while($row = $versions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['version']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['release_date'])); ?></td>
                                        <td><?php echo $row['notes']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No app versions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Version Modal -->
<div class="modal fade" id="addVersionModal" tabindex="-1" aria-labelledby="addVersionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVersionModalLabel">Add New App Version</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="admin.php?page=versions">
                    <div class="mb-3">
                        <label for="version" class="form-label">Version Number</label>
                        <input type="text" class="form-control" id="version" name="version" required placeholder="e.g. 1.2.0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Release Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Describe what's new in this version"></textarea>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_version" class="btn btn-primary">Add Version</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="export.php">
                    <div class="mb-3">
                        <label for="export_type" class="form-label">Select Data to Export</label>
                        <select class="form-select" id="export_type" name="export_type" required>
                            <option value="downloads">App Downloads</option>
                            <option value="purchases">Purchases</option>
                            <option value="versions">App Versions</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="format" class="form-label">Export Format</label>
                        <select class="form-select" id="format" name="format" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select class="form-select" id="date_range" name="date_range">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="export" class="btn btn-success">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
                    break;
                default:
                    // Default to dashboard
                    header("Location: admin.php?page=dashboard");
                    exit();
            }
?>
        </div>
    <?php else: ?>
        <!-- Login/Register Form -->
        <div class="auth-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <ul class="nav nav-pills nav-fill" id="adminTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" 
                                                data-bs-target="#login-pane" type="button" role="tab" 
                                                aria-controls="login-pane" aria-selected="true">
                                            <i class="fas fa-sign-in-alt me-2"></i> Login
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="register-tab" data-bs-toggle="tab" 
                                                data-bs-target="#register-pane" type="button" role="tab" 
                                                aria-controls="register-pane" aria-selected="false">
                                            <i class="fas fa-user-plus me-2"></i> Register
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content p-4" id="adminTabContent">
                                    <!-- Login Form -->
                                    <div class="tab-pane fade show active" id="login-pane" role="tabpanel" aria-labelledby="login-tab">
                                        <h4 class="text-center mb-4">Admin Login</h4>
                                        
                                        <?php if (isset($login_error)): ?>
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $login_error; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="post" action="">
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control" id="loginEmail" name="email" placeholder="name@example.com" required>
                                                <label for="loginEmail">Email address</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
                                                <label for="loginPassword">Password</label>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" name="login" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Register Form -->
                                    <div class="tab-pane fade" id="register-pane" role="tabpanel" aria-labelledby="register-tab">
                                        <h4 class="text-center mb-4">Admin Registration</h4>
                                        
                                        <?php if (isset($register_error)): ?>
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $register_error; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($register_success)): ?>
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle me-2"></i> <?php echo $register_success; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="post" action="">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="registerName" name="name" placeholder="Full Name" required>
                                                <label for="registerName">Full Name</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control" id="registerEmail" name="email" placeholder="name@example.com" required>
                                                <label for="registerEmail">Email address</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Password" required minlength="8">
                                                <label for="registerPassword">Password</label>
                                                <div class="form-text">Password must be at least 8 characters long.</div>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" name="register" class="btn btn-success btn-lg">
                                                    <i class="fas fa-user-plus me-2"></i> Register
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="bootstrap\bootstrap.bundle.min.js"></script>
    <script>
        // Populate edit modal with purchase data
        document.querySelectorAll('.edit-purchase').forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('edit-id').value = this.getAttribute('data-id');
                document.getElementById('edit-userid').value = this.getAttribute('data-userid');
                document.getElementById('edit-email').value = this.getAttribute('data-email');
                document.getElementById('edit-version').value = this.getAttribute('data-version');
                document.getElementById('edit-status').value = this.getAttribute('data-status');
            });
        });
    </script>
</body>
</html>