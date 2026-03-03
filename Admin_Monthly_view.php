<?php
session_start(); // Start the session

// Check if admin_id and location session variables are set
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['location'])) {
    // Redirect to login if session is not set
    header("Location: Admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$location = $_SESSION['location'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistics_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT incharge, profile, address, location,password FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
// Fetch all messages from the database
$sql = "SELECT * FROM chat_messages ORDER BY sent_time ASC";
$result = $conn->query($sql);
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $incharge = $_POST['incharge'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $password3 = $_POST['password3'];

    $profile_img = $admin['profile']; // Keep old profile by default

    // If a new image is uploaded
    if (!empty($_FILES['profile']['name'])) {
        $target_dir = "Asset/";
        $target_file = $target_dir . basename($_FILES["profile"]["name"]);
        move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file);
        $profile_img = $target_file; // Set new profile path
    }
    $pvalid = 0;
    // Update details in database
    if ($password2 == "") {
        $updateQuery = "UPDATE admin SET incharge = ?, address = ?,password = ?, profile = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssss", $incharge, $address, $password, $profile_img, $admin_id);
    } elseif ($password3 == $password) {
        $updateQuery = "UPDATE admin SET incharge = ?, address = ?,password = ?, profile = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssss", $incharge, $address, $password2, $profile_img, $admin_id);

    } else {
        $pvalid = 1;
    }
    if ($pvalid == 0) {
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-3'>Profile updated successfully</div>";
            header("Location: " . $_SERVER['PHP_SELF']);
        } else {
            echo "Error updating profile.";
        }
    } else {
        echo "<script>
        alert('Wrong old password');
        window.location.href = '" . $_SERVER['PHP_SELF'] . "';
      </script>";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$position2 = '';
$stmt = $conn->prepare("SELECT position FROM admin WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$stmt->bind_result($position2);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="Asset/Logo.png">
    <title>SV Logistics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap");

        * {
            font-family: "Open Sans", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --grey: #f1f0f6;
            --dark-grey: #8d8d8d;
            --light: #fff;
            --dark: #000;
            --green: #81d43a;
            --light-green: #e3ffcb;
            --blue: #2f3192;
            --light-blue: #d0e4ff;
            --dark-blue: #0c5fcd;
            --red: #fc3b56;
        }

        html {
            overflow-x: hidden;
        }

        body {
            background: var(--grey);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        li {
            list-style: none;
        }

        /* SIDEBAR */
        #sidebar {
            position: fixed;
            max-width: 260px;
            width: 100%;
            background: var(--light);
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            scrollbar-width: none;
            transition: all 0.3s ease;
            z-index: 200;
        }

        #sidebar.hide {
            max-width: 60px;
        }

        #sidebar.hide:hover {
            max-width: 260px;
        }

        #sidebar::-webkit-scrollbar {
            display: none;
        }

        #sidebar .brand {
            font-size: 22px;
            display: flex;
            align-items: center;
            height: 64px;
            font-weight: 700;
            color: var(--blue);
            position: sticky;
            top: 0;
            left: 0;
            z-index: 100;
            background: var(--blue);
            color: transparent !important;
            background-clip: text;
            transition: all 0.3s ease;
            padding: 0 6px;
        }

        #sidebar .brand span {
            color: #ea008b;
        }

        #sidebar .icon,
        #sidebar img {
            min-width: 48px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 6px;
        }

        #sidebar .icon-right {
            margin-left: auto;
            transition: all 0.3s ease;
        }

        #sidebar .side-menu {
            margin: 36px 0;
            padding: 0 20px;
            transition: all 0.3s ease;
        }

        #sidebar.hide .side-menu {
            padding: 0 6px;
        }

        #sidebar.hide:hover .side-menu {
            padding: 0 20px;
        }

        #sidebar .side-menu a {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--dark);
            padding: 12px 16px 12px 0;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin: 4px 0;
            white-space: nowrap;
        }

        #sidebar .side-menu>li>a:hover {
            background: var(--grey);
        }

        #sidebar .side-menu>li>a.active .icon-right {
            transform: rotateZ(90deg);
        }

        #sidebar .side-menu>li>a.active,
        #sidebar .side-menu>li>a.active:hover {
            background: var(--blue);
            color: var(--light);
        }

        #sidebar .divider {
            margin-top: 24px;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--dark-grey);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        #sidebar.hide:hover .divider {
            text-align: left;
        }

        #sidebar.hide .divider {
            text-align: center;
        }

        #sidebar .side-dropdown {
            padding-left: 54px;
            max-height: 0;
            overflow-y: hidden;
            transition: all 0.15s ease;
        }

        #sidebar .side-dropdown.show {
            max-height: 1000px;
        }

        #sidebar .side-dropdown a:hover {
            color: var(--blue);
        }

        #sidebar .ads {
            width: 100%;
            padding: 20px;
        }

        #sidebar.hide .ads {
            display: none;
        }

        #sidebar.hide:hover .ads {
            display: block;
        }

        #sidebar .ads .wrapper {
            background: var(--grey);
            padding: 20px;
            border-radius: 10px;
        }

        #sidebar .btn-upgrade {
            font-size: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 0;
            color: var(--light);
            background: var(--blue);
            transition: all 0.3s ease;
            border-radius: 5px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        #sidebar .ads .wrapper p {
            font-size: 12px;
            color: var(--dark-grey);
            text-align: center;
        }

        #sidebar .ads .wrapper p span {
            font-weight: 700;
        }

        /* SIDEBAR */

        /* CONTENT */
        #content {
            position: relative;
            width: calc(100% - 260px);
            left: 260px;
            transition: all 0.3s ease;
            overflow: visible !important;
        }

        #sidebar.hide+#content {
            width: calc(100% - 60px);
            left: 60px;
        }

        /* NAVBAR */
        nav {
            background: var(--light);
            height: 64px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            grid-gap: 28px;
            position: sticky !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 100;
        }

        nav .toggle-sidebar {
            font-size: 18px;
            cursor: pointer;
        }

        nav .nav-link {
            position: relative;
        }

        nav .nav-link .icon {
            font-size: 18px;
            color: var(--dark);
        }

        nav .nav-link .badge {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid var(--light);
            background: var(--red);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--light);
            font-size: 10px;
            font-weight: 700;
        }

        nav .divider {
            width: 1px;
            background: var(--grey);
            height: 12px;
            display: block;
        }

        nav .profile {
            position: relative;
        }

        nav .profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        nav .profile .profile-link {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--light);
            padding: 10px 0;
            box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            width: 160px;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        nav .profile .profile-link.show {
            opacity: 1;
            pointer-events: visible;
            top: 100%;
        }

        nav .profile .profile-link a {
            padding: 10px 16px;
            display: flex;
            grid-gap: 10px;
            font-size: 14px;
            color: var(--dark);
            align-items: center;
            transition: all 0.3s ease;
        }

        nav .profile .profile-link a:hover {
            background: var(--grey);
        }

        /* NAVBAR */

        /* MAIN */
        main {
            width: 100%;
            padding: 24px 20px 20px 20px;
        }

        main .title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        main .breadcrumbs {
            display: flex;
            grid-gap: 6px;
        }

        main .breadcrumbs li,
        main .breadcrumbs li a {
            font-size: 14px;
        }

        main .breadcrumbs li a {
            color: var(--blue);
        }

        main .breadcrumbs li a.active,
        main .breadcrumbs li.divider {
            color: var(--dark-grey);
            pointer-events: none;
        }

        .bubble-container {
            width: 100%;
            height: 200px;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            padding: 10px;
            gap: 10px;
            background-color: rgb(255, 255, 255);
        }

        .floating-box {
            display: inline-block;
            padding: 5px 10px;
            /* Add some padding inside the bubble */
            border: 1px solid #ddd;
            border-radius: 15px;
            background-color: #ffffff;
            position: relative;
            text-align: center;
            /* Center the content inside the bubble */
            font-size: 14px;
            /* Adjust the font size for readability */
            line-height: 1.2;
            /* Adjust line height to ensure compactness */
            width: 420px;
            /* Fixed width for each bubble */
            height: 60px;
            /* Fixed height to accommodate the content */
            white-space: nowrap;
            /* Prevent wrapping of the content */
            overflow: hidden;
            /* Prevent overflow if content is too large */
            display: flex;
            justify-content: center;
            align-items: center;
            /* Center content vertically and horizontally */
        }

        /* Remove (X) button - Positioned at the top-right corner */
        .floating-box .remove-btn {
            position: absolute;
            top: 0px;
            right: 3px;
            cursor: pointer;
            font-size: 24px;
            color: red;
            /* Red color for close (X) button */
            font-weight: bold;
            background: transparent;
            border: none;
            padding: 0;
        }

        /* Edit button - Positioned at the bottom-left corner */
        .floating-box .edit-btn {
            position: absolute;
            bottom: 2px;
            right: 3px;
            cursor: pointer;
            font-size: 22px;
            color: blue;
            /* Blue color for edit button */
            background: transparent;
            border: none;
            padding: 0;
        }

        .responsive-table {
            overflow-x: auto;
        }

        .compact-table {
            display: none;
        }

        #pdfframe {
            position: fixed;
            top: 20px;
            /* Adjust this to position the iframe higher or lower */
            left: 0;
            right: 0;
            width: 100%;
            height: 500px;
            border: 1px solid #ccc;
            display: block;
            /* Ensure it's visible */
            z-index: 1000;
            /* Make sure it's on top of other content */
        }

        .modal-lg {
            max-width: 90%;
            /* Set custom width to 90% of the screen width */
            width: 70%;
        }

        .alert {
            position: fixed;
            top: -100px;
            /* Initially hidden above the viewport */
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 10px;
            background-color: rgba(47, 49, 146, 0.31);
            color: rgb(0, 0, 0);
            border: 1px solid rgb(0, 0, 0);
            border-radius: 5px;
            animation: slideDownUp 3s ease-in-out forwards;
            /* 3-second animation */
        }

        @keyframes slideDownUp {
            0% {
                top: -100px;
                /* Start above the viewport */
                opacity: 0;
            }

            20% {
                top: 20px;
                /* Center of the screen (adjust as needed) */
                opacity: 1;
            }

            80% {
                top: 20px;
                /* Stay in the center */
                opacity: 1;
            }

            100% {
                top: -100px;
                /* Slide back up */
                opacity: 0;
            }
        }

        /* MAIN */

        @media screen and (max-width:1000px) {
            .full-table {
                display: none;
            }

            .compact-table {
                display: table;
            }

            #content {
                position: relative;
                width: calc(100% - 60px);
                transition: all 0.3s ease;
            }

            nav .nav-link,
            nav .divider {
                display: none;
            }
        }

        .form-container {
            max-width: 1210px !important;
            background-color: #2f31922a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        label {
            font-weight: 600;
        }

        .input-table th,
        .input-table td {
            padding: 8px;
            text-align: left;
        }

        .radio-group {
            margin-top: 15px;
        }

        .btn-submit {
            background-color: var(--red);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
        }

        .btn-submit:hover {
            background-color: #0c5fcd;
            color: white;
        }

        .small-note {
            font-size: 12px;
            color: #777;
        }

        .custom-button {
            padding: 10px 20px;
            background-color: var(--blue);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out;
            margin: 10px auto;
            display: block;
        }

        .custom-input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease-in-out;
        }

        .custom-input:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Spacing for better form layout */
        .form-group {
            margin-bottom: 15px;
            display: block;
            align-items: center;
            gap: 10px;
        }

        .custom-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        /* Modal Content */
        .custom-modal-content {
            background: #fff;
            width: 40%;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Close Button */
        .close-modal {
            float: right;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            color: #333;
        }

        .close-modal:hover {
            color: red;
        }

        /* Form Styling */
        .custom-form label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }

        .custom-form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: 0.3s;
        }

        .custom-form input:focus {
            border-color: #007BFF;
            outline: none;
            box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
        }

        /* Save Button */
        .modal-save-btn {
            background: #2f3192;
            color: white;
            border: none;
            padding: 10px;
            margin-top: 15px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal-save-btn:hover {
            background: #2f3192;
        }

        @media (max-width: 1068px) {}
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand"><img src="./Asset/sv_logistics-removebg-preview.png" alt="" width="35" /> <span>SV
            </span>&nbsp; Logistics</a>
        <ul class="side-menu">
            <li>
                <a href="Admin_Dashboard.php"><i class="bx bxs-dashboard icon"></i> Dashboard</a>
            </li>
            <li class="divider" data-text="Entrys">Entrys</li>
            <li>
                <a href="#"><i class="bx bxs-inbox icon"></i> Operation <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown ">
                    <li><a href="Admin_Addbill.php">Waybills</a></li>
                    <li><a href="Admin_Todaybill.php" style="color: #2f3192">Today Waybills</a></li>
                    <li><a href="Admin_Totalbill.php">Total Waybills</a></li>
                </ul>
            </li>

            <li class="divider" data-text="Manifest">Manifest</li>
            <li>
                <a href="#"><i class="bx bxs-inbox icon"></i> Transits <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown">
                    <li><a href="Admin_Intransit.php">In Transits</a></li>
                    <li><a href="Admin_VehicleOUT.php">Vehicle OUT</a></li>
                    <li><a href="Admin_VehicleIN.php">Vehicle IN</a></li>
                    <li><a href="Admin_Searchvehicle.php">Vehicle Search</a></li>
                </ul>
            </li>
            <li class="divider" data-text="Delivery">Delivery</li>
            <li>
                <a href="#"><i class="bx bxs-inbox icon"></i> Delivery <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown">
                    <li><a href="Admin_Delivery.php">InDelivery</a></li>
                    <li><a href="Admin_Completed.php">Completed</a></li>

                </ul>
            </li>
            <li class="divider" data-text="Monthly">Monthly bills</li>
            <li>
                <a href="#" class="active"><i class="bx bxs-inbox icon"></i> Monthly <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown show">
                    <li><a href="Admin_Monthly.php">Add Monthly Bill</a></li>
                    <li><a href="Admin_Monthly_view.php">View Monthly Bills</a></li>

                </ul>
            </li>

        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class="bx bx-menu toggle-sidebar"></i>
            <div class="profile">
                <span><?php echo htmlspecialchars($admin['incharge']); ?> /
                    <?php echo htmlspecialchars($admin['location']); ?></span>
                <img src="./<?php echo htmlspecialchars($admin['profile']); ?>" alt="Profile Image"
                    class="admin-profile" style="border:solid 0.5px green;" />


                <ul class="profile-link">
                    <li><a href="#" id="open-settings"><i class="bx bxs-cog"></i> Settings</a></li>
                    <li><a href="?logout=true"><i class="bx bxs-log-out-circle"></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Custom Modal -->
        <div class="custom-modal" id="settings-modal">
            <div class="custom-modal-content">
                <span class="close-modal">&times;</span>
                <h2>Edit Profile</h2>
                <form action="" method="POST" enctype="multipart/form-data" class="custom-form">
                    <label for="edit-incharge">Name:</label>
                    <input type="text" id="edit-incharge" name="incharge"
                        value="<?php echo htmlspecialchars($admin['incharge']); ?>" required>

                    <label for="edit-address">Address:</label>
                    <input type="text" id="edit-address" name="address"
                        value="<?php echo htmlspecialchars($admin['address']); ?>" required>

                    <label for="edit-location">Change password</label>
                    <input type="password" name="password3" value="" placeholder="Enter old password">
                    <input type="password" name="password2" value="" placeholder="Enter New password">
                    <input type="hidden" name="password" value="<?php echo htmlspecialchars($admin['password']); ?>"
                        required>

                    <label for="edit-profile">Profile Image:</label>
                    <input type="file" id="edit-profile" name="profile">

                    <button type="submit" name="update_profile" class="modal-save-btn">Save Changes</button>
                </form>
            </div>
        </div>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <h1 class="title">Dashboard</h1>
            <ul class="breadcrumbs p-0">
                <li><a href="#">Waybills</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Operation</a></li>
            </ul>
        </main>
        <!-- MAIN -->
        <div class="container form-container">
            <!-- Search -->
            <form action="" method="get" class="mt-3 mb-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="searchQuery" class="form-control"
                            placeholder="Search by Particular, or Ack No"
                            value="<?= isset($_GET['searchQuery']) ? htmlspecialchars($_GET['searchQuery']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Search</button>
                    </div>
                </div>
            </form>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="invoiceTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active"
                        type="button">Active</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled"
                        type="button">Cancelled</button>
                </li>
            </ul>

            <div class="tab-content mt-4" id="invoiceTabsContent">

                <!-- Active Invoices -->
                <div class="tab-pane fade show active" id="active" role="tabpanel">
                    <?php
                    $baseQueryActive = "SELECT customerName, invoiceNo, particular, total, ackno 
                FROM monthlyreport WHERE status!='Cancelled'";

                    if (!empty($_GET['searchQuery'])) {
                        $search = $conn->real_escape_string($_GET['searchQuery']);
                        $baseQueryActive .= " AND (ackno LIKE '%$search%' OR particular LIKE '%$search%' OR customerName LIKE '%$search%')";
                    }
                    $resultActive1 = $conn->query($baseQueryActive); // for full-table
                    $resultActive2 = $conn->query($baseQueryActive); // for compact-table
                    ?>

                    <h2>Active Invoice Records</h2>
                    <div class="responsive-table mt-3">
                        <!-- Full Table -->
                        <table class="table table-bordered full-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Invoice No</th>
                                    <th>Particular</th>
                                    <th>Total</th>
                                    <th colspan="3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultActive1->fetch_assoc()) {
                                    $pdfFile = './Monthly_bills/' . $row['ackno'] . '.pdf'; ?>
                                    <tr>
                                        <td><?= $row['customerName'] ?></td>
                                        <td><?= $row['invoiceNo'] ?></td>
                                        <td><?= $row['particular'] ?></td>
                                        <td><?= $row['total'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewModal<?= $row['ackno'] ?>">View</button>
                                        </td>
                                        <td><button class="btn btn-warning btn-sm"
                                                onclick="handleButtonClick('<?= $row['ackno'] ?>')">Edit</button></td>
                                        <td><button class="btn btn-danger btn-sm"
                                                onclick="handleButtonClick2('<?= $row['ackno'] ?>')">Cancel</button></td>
                                    </tr>

                                    <!-- Modal -->
                                    <div class="modal fade" id="viewModal<?= $row['ackno'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">View PDF for ackno <?= $row['ackno'] ?></h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <iframe src="<?= $pdfFile ?>"
                                                        style="width:100%; height:70vh; border:1px solid #ccc;"></iframe>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Compact Table -->
                        <table class="table table-bordered compact-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Invoice No</th>
                                    <th>Particular</th>
                                    <th colspan="3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultActive2->fetch_assoc()) {
                                    $pdfFile = './Monthly_bills/' . $row['ackno'] . '.pdf'; ?>
                                    <tr>
                                        <td><?= $row['customerName'] ?></td>
                                        <td><?= $row['invoiceNo'] ?></td>
                                        <td><?= $row['particular'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewModal<?= $row['ackno'] ?>">View</button>
                                        </td>
                                        <td><button class="btn btn-warning btn-sm"
                                                onclick="handleButtonClick('<?= $row['ackno'] ?>')">Edit</button></td>
                                        <td><button class="btn btn-danger btn-sm"
                                                onclick="handleButtonClick2('<?= $row['ackno'] ?>')">Cancel</button></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Cancelled Invoices -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <?php
                    $baseQueryCancelled = "SELECT customerName, invoiceNo, particular, total, ackno 
                FROM monthlyreport WHERE status='Cancelled'";

                    if (!empty($_GET['searchQuery'])) {
                        $search = $conn->real_escape_string($_GET['searchQuery']);
                        $baseQueryCancelled .= " AND (ackno LIKE '%$search%' OR particular LIKE '%$search%' OR customerName LIKE '%$search%')";
                    }
                    $resultCancelled1 = $conn->query($baseQueryCancelled);
                    $resultCancelled2 = $conn->query($baseQueryCancelled);
                    ?>

                    <h2>Cancelled Invoice Records</h2>
                    <div class="responsive-table mt-3">
                        <!-- Full Table -->
                        <table class="table table-bordered full-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Invoice No</th>
                                    <th>Particular</th>
                                    <th>Total</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultCancelled1->fetch_assoc()) {
                                    $pdfFile = './Monthly_bills/' . $row['ackno'] . '.pdf'; ?>
                                    <tr>
                                        <td><?= $row['customerName'] ?></td>
                                        <td><?= $row['invoiceNo'] ?></td>
                                        <td><?= $row['particular'] ?></td>
                                        <td><?= $row['total'] ?></td>
                                        <div class="modal fade" id="viewModal<?= $row['ackno'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">View PDF for ackno <?= $row['ackno'] ?></h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <iframe src="<?= $pdfFile ?>"
                                                            style="width:100%; height:70vh; border:1px solid #ccc;"></iframe>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewModal<?= $row['ackno'] ?>">View</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Compact Table -->
                        <table class="table table-bordered compact-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Invoice No</th>
                                    <th>Particular</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $resultCancelled2->fetch_assoc()) {
                                    $pdfFile = './Monthly_bills/' . $row['ackno'] . '.pdf'; ?>
                                    <tr>
                                        <td><?= $row['customerName'] ?></td>
                                        <td><?= $row['invoiceNo'] ?></td>
                                        <td><?= $row['particular'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#viewModal<?= $row['ackno'] ?>">View</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        </form>
        <form id="hiddenForm" action="edit_monthly.php" method="POST">
            <input type="hidden" name="ackno" id="ackno" value="">
            <!-- The hidden form will be submitted automatically by the JavaScript function -->
        </form>
        <form id="hiddenForm2" action="cancel_monthly.php" method="POST">
            <input type="hidden" name="ackno2" id="ackno2" value="">
            <!-- The hidden form will be submitted automatically by the JavaScript function -->
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.getElementById("open-settings").addEventListener("click", function () {
            document.getElementById("settings-modal").style.display = "block";
        });

        document.querySelector(".close-modal").addEventListener("click", function () {
            document.getElementById("settings-modal").style.display = "none";
        });

        window.onclick = function (event) {
            if (event.target == document.getElementById("settings-modal")) {
                document.getElementById("settings-modal").style.display = "none";
            }
        };
        function handleButtonClick(billid) {

            // Set the hidden input value
            document.getElementById('ackno').value = billid;

            // Submit the form automatically
            document.getElementById('hiddenForm').submit();
        }
        function handleButtonClick2(billid) {

            // Set the hidden input value
            document.getElementById('ackno2').value = billid;

            // Submit the form automatically
            document.getElementById('hiddenForm2').submit();
        }

    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {


            function checkScreenSize() {
                if (window.innerWidth <= 1000) {



                    if (sidebar.classList.contains("hide")) {

                        allSideDivider.forEach((item) => {
                            item.textContent = "____";
                        });

                        allDropdown.forEach((item) => {
                            const a = item.parentElement.querySelector("a:first-child");
                            a.classList.remove("active");
                            item.classList.remove("show");
                        });
                    }
                    else {
                        sidebar.classList.toggle("hide");
                    }

                } else {
                    sidebar.classList.remove("hide");
                    allSideDivider.forEach((item) => {
                        item.textContent = item.dataset.text;
                    })
                }
            }
            // Initial check on load
            checkScreenSize();

            // Listen for window resize
            window.addEventListener('resize', checkScreenSize);
        });
        // SIDEBAR DROPDOWN
        const allDropdown = document.querySelectorAll("#sidebar .side-dropdown");
        const sidebar = document.getElementById("sidebar");

        allDropdown.forEach((item) => {
            const a = item.parentElement.querySelector("a:first-child");
            a.addEventListener("click", function (e) {
                e.preventDefault();

                if (!this.classList.contains("active")) {
                    allDropdown.forEach((i) => {
                        const aLink = i.parentElement.querySelector("a:first-child");

                        aLink.classList.remove("active");
                        i.classList.remove("show");
                    });
                }

                this.classList.toggle("active");
                item.classList.toggle("show");
            });
        });

        // SIDEBAR COLLAPSE
        const toggleSidebar = document.querySelector("nav .toggle-sidebar");
        const allSideDivider = document.querySelectorAll("#sidebar .divider");

        if (sidebar.classList.contains("hide")) {
            allSideDivider.forEach((item) => {
                item.textContent = "___";
            });
            allDropdown.forEach((item) => {
                const a = item.parentElement.querySelector("a:first-child");
                a.classList.remove("active");
                item.classList.remove("show");
            });
        } else {
            allSideDivider.forEach((item) => {
                item.textContent = item.dataset.text;
            });
        }

        toggleSidebar.addEventListener("click", function () {
            sidebar.classList.toggle("hide");

            if (sidebar.classList.contains("hide")) {
                allSideDivider.forEach((item) => {
                    item.textContent = "____";
                });

                allDropdown.forEach((item) => {
                    const a = item.parentElement.querySelector("a:first-child");
                    a.classList.remove("active");
                    item.classList.remove("show");
                });
            } else {
                allSideDivider.forEach((item) => {
                    item.textContent = item.dataset.text;
                });
            }
        });

        sidebar.addEventListener("mouseleave", function () {
            if (this.classList.contains("hide")) {
                allDropdown.forEach((item) => {
                    const a = item.parentElement.querySelector("a:first-child");
                    a.classList.remove("active");
                    item.classList.remove("show");
                });
                allSideDivider.forEach((item) => {
                    item.textContent = "___";
                });
            }
        });


        sidebar.addEventListener("mouseenter", function () {
            if (this.classList.contains("hide")) {
                allDropdown.forEach((item) => {
                    const a = item.parentElement.querySelector("a:first-child");
                    a.classList.remove("active");
                    item.classList.remove("show");
                });
                allSideDivider.forEach((item) => {
                    item.textContent = item.dataset.text;
                });
            }
        });

        // PROFILE DROPDOWN
        const profile = document.querySelector("nav .profile");
        const imgProfile = profile.querySelector("img");
        const dropdownProfile = profile.querySelector(".profile-link");

        imgProfile.addEventListener("click", function () {
            dropdownProfile.classList.toggle("show");
        });

        // MENU
        const allMenu = document.querySelectorAll("main .content-data .head .menu");

        allMenu.forEach((item) => {
            const icon = item.querySelector(".icon");
            const menuLink = item.querySelector(".menu-link");

            icon.addEventListener("click", function () {
                menuLink.classList.toggle("show");
            });
        });

        window.addEventListener("click", function (e) {
            if (e.target !== imgProfile) {
                if (e.target !== dropdownProfile) {
                    if (dropdownProfile.classList.contains("show")) {
                        dropdownProfile.classList.remove("show");
                    }
                }
            }

            allMenu.forEach((item) => {
                const icon = item.querySelector(".icon");
                const menuLink = item.querySelector(".menu-link");

                if (e.target !== icon) {
                    if (e.target !== menuLink) {
                        if (menuLink.classList.contains("show")) {
                            menuLink.classList.remove("show");
                        }
                    }
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.rowCheckbox');

            selectAllCheckbox.addEventListener('change', function () {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>