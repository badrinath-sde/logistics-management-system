<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli('localhost', 'root', '', 'logistics_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    $admin_id = $_POST['admin_id'];
    $password = $_POST['password'];
    $location = ucwords(strtolower(trim($_POST['location'])));
    $address = $_POST['address'];
    $incharge = $_POST['incharge'];
    $position = $_POST['position'];

    // Profile image logic
    $uploadDir = "Asset/";
    $defaultProfile = $uploadDir . "Logo.png"; // Make sure this exists
    $profilePath = $defaultProfile;

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("admin_", true) . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['profile']['tmp_name'], $targetFile)) {
            $profilePath = $targetFile;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO admin (admin_id, password, location, Address, Incharge, profile,position) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $admin_id, $password, $location, $address, $incharge, $profilePath, $position);

    if ($stmt->execute()) {
        echo "<script>alert('Admin added successfully'); window.history.back();</script>";
    } else {
        echo "<script>alert('Error adding admin'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6ff;
        }

        header {

            padding: 15px 30px;
            color: #2f3192;
            display: flex;
            align-items: center;
            align-items: center;
            justify-content: center;
            border-bottom: #2f3192 1px solid;
        }

        header img {

            height: 70px;
            margin-right: 15px;
        }

        footer {
            background-color: #2f3192;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 40px;
            bottom: 0;
            position: absolute;
            width: 100%;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            width: 70%;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 40px;
            width: 70%;
        }

        .btn-success {
            background-color: #2f3192;
            border: none;
        }

        .btn-success:hover {
            background-color: #23256f;
        }
    </style>
</head>

<body>

    <header>
        <img src="Asset/Logo.png" alt="Logo"> <!-- Replace with actual logo path -->

    </header>

    <div class="container">
        <div class="form-container">
            <h4 class="mb-4 text-center text-primary">Add New Admin</h4>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="admin_id" placeholder="Admin ID" required class="form-control my-2">
                <input type="password" name="password" placeholder="Password" required class="form-control my-2">
                <input type="text" name="location" placeholder="Location" required class="form-control my-2">
                <input type="text" name="address" placeholder="Address" required class="form-control my-2">
                <input type="text" name="incharge" placeholder="Name" required class="form-control my-2">
                <select name="position" required class="form-select my-2">
                    <option value="">-- Select Position --</option>
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>

                <label class="form-label">Profile Image (optional)</label>
                <input type="file" name="profile" accept="image/*" class="form-control my-2">

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-success px-4">Add Admin</button>
                    <a href="Admin_Dashboard.php" class="btn btn-outline-danger">
                        ← Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Admin Management System
    </footer>

</body>

</html>