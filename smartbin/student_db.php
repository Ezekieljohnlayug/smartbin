<?php
// Establish database connection
$db = new mysqli("localhost", "root", "", "student_db");

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Initialize variables
$id = "656";
$full_name = "";
$course_year = "";
$age = "";
$dob = "";
$email = "";
$address = "";
$average_grade = "";
$edit_state = false;

// Handle form submission (Insert or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $course_year = trim($_POST['course_year']);
    $age = intval($_POST['age']);
    $dob = $_POST['dob'];
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $average_grade = floatval($_POST['average_grade']);

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('<script>alert("Invalid email format."); window.history.back();</script>');
    }
    if ($average_grade < 0 || $average_grade > 100) {
        die('<script>alert("Average grade must be between 0 and 100."); window.history.back();</script>');
    }
    if ($age < 1 || $age > 120) {
        die('<script>alert("Age must be between 1 and 120."); window.history.back();</script>');
    }

    if (isset($_POST['update'])) {
        // Update user
        $id = intval($_POST['id']);
        $stmt = $db->prepare("UPDATE user SET full_name=?, course_year=?, age=?, dob=?, email=?, address=?, average_grade=? WHERE id=?");
        $stmt->bind_param("ssisssdi", $full_name, $course_year, $age, $dob, $email, $address, $average_grade, $id);
    } else {
        // Insert new user
        $stmt = $db->prepare("INSERT INTO user (full_name, course_year, age, dob, email, address, average_grade) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssd", $full_name, $course_year, $age, $dob, $email, $address, $average_grade);
    }

    if ($stmt->execute()) {
        header("Location: student_db.php"); // Auto-refresh page
        exit();
    } else {
        die('<script>alert("Database error occurred."); window.history.back();</script>');
    }
}

// Handle Edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit_state = true;
    $stmt = $db->prepare("SELECT * FROM user WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($user = $result->fetch_assoc()) {
        $full_name = $user['full_name'];
        $course_year = $user['course_year'];
        $age = $user['age'];
        $dob = $user['dob'];
        $email = $user['email'];
        $address = $user['address'];
        $average_grade = $user['average_grade'];
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM user WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: student_db.php"); // Auto-refresh page after delete
        exit();
    } else {
        die('<script>alert("Error deleting user."); window.history.back();</script>');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/abellon.css">
    <title>PHP and SQL - User Registration</title>
</head>
<body>

    <div class="wrapper"> <!-- Wrapper div starts -->

        <h2>Student  Registration</h2>

        <form method="post">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" placeholder="Enter Full Name" required>
            <br><br>

            <label>Course & Year</label>
            <input type="text" name="course_year" value="<?= htmlspecialchars($course_year) ?>" placeholder="Enter Course and Year" required>
            <br><br>

            <label>Age</label>
            <input type="number" name="age" value="<?= htmlspecialchars($age) ?>" placeholder="Enter Age" required min="1" max="120">
            <br><br>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
            <br><br>

            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter Email Address" required>
            <br><br>

            <label>Home Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" placeholder="Enter Home Address" required>
            <br><br>

            <label>Average Grade</label>
            <input type="number" step="0.01" name="average_grade" value="<?= htmlspecialchars($average_grade) ?>" placeholder="Enter Average Grade" required min="0" max="100">
            <br><br>

            <?php if ($edit_state): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="submit" name="update" value="Update">
            <?php else: ?>
                <input type="submit" name="submit" value="Submit">
            <?php endif; ?>
            <hr>
        </form>

        <h3>User List</h3>
        <table style="width: 80%" border="1">
            <tr>
                <th>User Number</th>
                <th>Full Name</th>
                <th>Course & Year</th>
                <th>Age</th>
                <th>Date of Birth</th>
                <th>Email</th>
                <th>Address</th>
                <th>Average Grade</th>
                <th>Operations</th>
            </tr>

            <?php
            // Fetch and display users
            $stmt = $db->prepare("SELECT * FROM user");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $i++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['course_year']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['age']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dob']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['average_grade']) . "</td>";
                    echo "<td><a href='?edit=". $row['id'] . "'>Edit</a> | <a href='?delete=" . $row['id'] . "' onClick=\"return confirm('Are you sure?')\">Delete</a></td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
        <hr>

   

</body>
</html>