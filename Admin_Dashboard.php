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
$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tasks for the selected date from the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedDate'])) {
    $selectedDate = $_POST['selectedDate'];

    $stmt = $conn->prepare("SELECT id, task FROM tasks WHERE selected_date = ?");
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($tasks);
    exit;
}

// If it's a POST request for updating a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateTask']) && isset($_POST['taskId'])) {
    $updatedTask = $_POST['updateTask'];
    $taskId = intval($_POST['taskId']); // Ensure it's an integer

    error_log("Updating Task ID: $taskId with Task: $updatedTask"); // Debugging

    $stmt = $conn->prepare("UPDATE tasks SET task = ? WHERE id = ?");
    $stmt->bind_param("si", $updatedTask, $taskId);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// If it's a POST request for deleting a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteTaskId'])) {
    $taskId = intval($_POST['deleteTaskId']); // Ensure it's an integer

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// Get total invoice count
$totalInvoicesQuery = "SELECT COUNT(*) AS total FROM invoices";
$totalInvoices = $conn->query($totalInvoicesQuery)->fetch_assoc()['total'];

// Get current month invoice count
$currentMonthQuery = "SELECT COUNT(*) AS current_month FROM invoices WHERE MONTH(billdate) = MONTH(CURRENT_DATE()) AND YEAR(billdate) = YEAR(CURRENT_DATE())";
$currentMonth = $conn->query($currentMonthQuery)->fetch_assoc()['current_month'];

// Get previous month invoice count
$previousMonthQuery = "SELECT COUNT(*) AS previous_month FROM invoices WHERE MONTH(billdate) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(billdate) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
$previousMonth = $conn->query($previousMonthQuery)->fetch_assoc()['previous_month'];

// Get current week invoice count
$currentWeekQuery = "SELECT COUNT(*) AS current_week FROM invoices WHERE YEARWEEK(billdate, 1) = YEARWEEK(CURRENT_DATE(), 1)";
$currentWeek = $conn->query($currentWeekQuery)->fetch_assoc()['current_week'];

// Get previous week invoice count
$previousWeekQuery = "SELECT COUNT(*) AS previous_week FROM invoices WHERE YEARWEEK(billdate, 1) = YEARWEEK(CURRENT_DATE() - INTERVAL 1 WEEK, 1)";
$previousWeek = $conn->query($previousWeekQuery)->fetch_assoc()['previous_week'];

// Get today's invoice count
$todayQuery = "SELECT COUNT(*) AS today FROM invoices WHERE DATE(billdate) = CURRENT_DATE()";
$today = $conn->query($todayQuery)->fetch_assoc()['today'];

// Determine if counts increased or decreased
$monthTrend = ($currentMonth >= $previousMonth) ? "bx-trending-up" : "bx-trending-down down";
$weekTrend = ($currentWeek >= $previousWeek) ? "bx-trending-up" : "bx-trending-down down";


$from_date = '';
$to_date = '';
$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excel'])) {
    // Get the date range from the form
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];

    // Create the SQL query to fetch data within the selected date range
    $sql = "
        SELECT 
            billid, ref1, consignor_name, consignor_phone, consignor_email, consignor_gstin, consignor_panin, 
            consignor_address, consignor_district, consignor_state, consignor_pincode, consignee_name, consignee_phone, 
            consignee_email, consignee_gstin, consignee_panin, consignee_address, consignee_district, consignee_state, 
            consignee_pincode, no_of_articles, invoice_no, invoice_date, ewaybill_no, said_to_contain, actual_weight, 
            charged_weight, goods_value, value_sep, basic_freight, document_charge, other_charge, fuel_surcharge, 
            handling_charge, door_collection, door_delivery, total_freight, gst_amount, grand_total, apply_gst, 
            date_time, paymentMode, created_by, billdate, transit_status
        FROM invoices
        WHERE billdate BETWEEN '$from_date' AND '$to_date'
    ";

    // Execute the query and fetch the data
    $result = mysqli_query($conn, $sql);

    // If records are found, generate the Excel file
    if (mysqli_num_rows($result) > 0) {
        // Output headers for Excel file (CSV format)
        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];

        // Sanitize the dates to avoid any issues in the filename
        $from_date = date('Y-m-d', strtotime($from_date)); // Format date as Y-m-d
        $to_date = date('Y-m-d', strtotime($to_date));

        // Combine the dates for the filename (you can adjust the format if needed)
        $filename = "bill_details_{$from_date}_to_{$to_date}.csv";

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        // Open the file for output
        $output = fopen('php://output', 'w');

        // Add column headers
        fputcsv($output, [
            'Bill ID',
            'Ref1',
            'Consignor Name',
            'Consignor Phone',
            'Consignor Email',
            'Consignor GSTIN',
            'Consignor PAN',
            'Consignor Address',
            'Consignor District',
            'Consignor State',
            'Consignor Pincode',
            'Consignee Name',
            'Consignee Phone',
            'Consignee Email',
            'Consignee GSTIN',
            'Consignee PAN',
            'Consignee Address',
            'Consignee District',
            'Consignee State',
            'Consignee Pincode',
            'No of Articles',
            'Invoice No',
            'Invoice Date',
            'Ewaybill No',
            'Said to Contain',
            'Actual Weight',
            'Charged Weight',
            'Goods Value',
            'Value Sep',
            'Basic Freight',
            'Document Charge',
            'Other Charge',
            'Fuel Surcharge',
            'Handling Charge',
            'Door Collection',
            'Door Delivery',
            'Total Freight',
            'GST Amount',
            'Grand Total',
            'Apply GST',
            'Date Time',
            'Payment Mode',
            'Created By',
            'Bill Date',
            'Transit Status'
        ]);

        // Fetch rows and output them
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        echo "<div class='alert alert-success mt-3'>No data found for the Selected Dates</div>";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $location = $_POST['location2'];
    // Insert message into the database
    $sql = "INSERT INTO chat_messages (message, location) VALUES ('$message', '$location')";
    if ($conn->query($sql) === TRUE) {
        $_POST['message'] = "";
        echo `<script>window.href='Admin_Dashboard.php'</script>`;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
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
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="Asset/Logo.png">
    <title>SV Logistics</title>
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

        main .info-data {
            margin-top: 36px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            grid-gap: 20px;
        }

        main .info-data .card {
            padding: 20px;
            border-radius: 10px;
            background: var(--light);
            box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.05);
        }

        main .card .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        main .card .head h2 {
            font-size: 34px;
            font-weight: 600;
        }

        main .card .head p {
            font-size: 18px;
        }

        main .card .head .icon {
            font-size: 20px;
            color: var(--green);
        }

        main .card .head .icon.down {
            color: var(--red);
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
        /* CONTENT */

        @media screen and (max-width: 768px) {
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

        /*main end*/
        /*calender start*/
        .second-conatainer {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            align-items: start;
            width: 100% !important;
        }

        .calendar-container {
            background: #fff;
            color: #333;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 350px;
            min-height: 450px !important;
            max-height: 450px !important;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--blue);
            color: #fff;
        }

        .calendar-header h2 {
            font-size: 1rem;
        }

        .calendar-header .icons {
            display: flex;
            gap: 10px;
        }

        .calendar-header .icons span {
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .calendar-header .icons span:hover {
            transform: scale(1.2);
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            padding: 5px;
            gap: 1px;
            text-align: center;
        }

        .calendar-days div {
            font-weight: bold;
            color: var(--blue);
        }

        .calendar-dates {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            padding: 5px;
            gap: 1px;
        }

        .calendar-dates div {
            height: 43px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
            position: relative;
        }

        .calendar-dates div:hover {
            background: #f0f0f0;
            border-radius: 50%;
        }

        .calendar-dates div.active {
            background: var(--blue);
            color: #fff;
            border-radius: 50%;
        }

        .todo-list {
            border-top: 1px solid #eee;
            padding: 5px 10px;
            background: #f9f9f9;
            max-height: 150px;
            overflow-y: auto;
            margin-top: auto;
        }

        .todo-list h3 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .todo-input {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .todo-input input {
            flex: 1;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .todo-input button {
            background: var(--blue);
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .todo-input button:hover {
            background: var(--blue);
        }

        /*calender end*/
        .todo-conatiner {
            width: 350px;
            min-height: 450px !important;
            max-height: 450px !important;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .todo-header {
            background: var(--blue);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .excel-download {
            width: 350px;
            min-height: 450px !important;
            max-height: 450px !important;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .excel-download .form-group,
        .excel-download .custom-input {
            color: black;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Center label, input, and button */
            justify-content: center;
            /* Ensure inner content is centered */
            text-align: center;
            margin-bottom: 15px;
        }

        .excel-download .custom-button {
            color: white;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Center label, input, and button */
            justify-content: center;
            /* Ensure inner content is centered */
            text-align: center;
            margin-bottom: 15px;
        }

        .excel-header {
            background: var(--blue);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 500;
        }

        #todo-list {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
            width: 100%;
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
        }

        .todo-list li {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            line-height: 1.4;

            background: #eee;
            color: #333;
        }

        /*chat box*/
        .chat-box {
            width: 450px;
            min-height: 450px !important;
            max-height: 450px !important;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: var(--blue);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9f9;
        }

        .message {
            margin: 10px 0;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .message.sent {
            background: var(--blue);
            color: #fff;
            margin-left: auto;
        }

        .message.received {
            background: #eee;
            color: #333;
            margin-right: auto;
        }

        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ddd;
            background: #fff;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }

        .chat-input button {
            background: var(--blue);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .chat-input button:hover {
            background: #2575fc;
        }


        /* Fade-in Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /*chat box end*/
        @media (max-width: 1062px) {
            .second-conatainer {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand"><img src="./Asset/sv_logistics-removebg-preview.png" alt="" width="35" /> <span>SV
            </span>&nbsp; Logistics</a>
        <ul class="side-menu">
            <li>
                <a href="Admin_Dashboard.php" class="active"><i class="bx bxs-dashboard icon"></i> Dashboard</a>
            </li>
            <li class="divider" data-text="Entrys">Entrys</li>
            <li>
                <a href="#"><i class="bx bxs-inbox icon"></i> Operation <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown">
                    <li><a href="Admin_Addbill.php">Waybills</a></li>
                    <li><a href="Admin_Todaybill.php">Today Waybills</a></li>
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
            <li>
                <a href="#"><i class="bx bxs-inbox icon"></i> Monthly <i class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown">
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
                    <?php if (strtolower($position2) === 'admin'): ?>
                        <li><a href="admin_add_form.php"><i class="bx bxs-user-plus"></i> Register New Staff</a></li>
                    <?php endif; ?>
                    <li><a href="?logout=true"><i class="bx bxs-log-out-circle"></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Custom Modal -->
        <div class="custom-modal" id="settings-modal">
            <div class="custom-modal-content">0
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
            <ul class="breadcrumbs">
                <li><a href="#">Home</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Dashboard</a></li>
            </ul>

            <div class="info-data">
                <div class="card">
                    <div class="head">
                        <div>
                            <h2><?php echo $totalInvoices; ?></h2>
                            <p>Total</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="head">
                        <div>
                            <h2><?php echo $currentMonth; ?></h2>
                            <p>This Month</p>
                        </div>
                        <i class="bx <?php echo $monthTrend; ?> icon"></i> <!-- Dynamically set icon -->
                    </div>
                </div>

                <div class="card">
                    <div class="head">
                        <div>
                            <h2><?php echo $today; ?></h2>
                            <p>Today</p>
                        </div>
                        <i class="bx <?php echo $weekTrend; ?> icon"></i> <!-- Dynamically set icon -->
                    </div>
                </div>
            </div>

            <div class="second-conatainer">
                <div class="calendar-container">
                    <div class="calendar-header">
                        <h2 id="current-month">January 2025</h2>
                        <div class="icons">
                            <span id="prev-month">❮</span>
                            <span id="next-month">❯</span>
                        </div>
                    </div>
                    <div class="calendar-days">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-dates" id="calendar"></div>
                    <div class="todo-list">
                        <h3>To-Do List</h3>
                        <div class="todo-input">
                            <input type="text" id="todo-input" placeholder="Add a new task..." />
                            <button id="add-todo" type="button">Add</button>
                        </div>
                    </div>
                </div>
                <div class="todo-conatiner">
                    <div class="todo-header">Daily Todos</div>
                    <ul id="todo-list">
                        <li>No tasks for today.</li>
                    </ul>
                </div>



                <div class="chat-box">
                    <div class="chat-header">Chat with Us</div>
                    <div class="chat-messages" id="chat-messages">
                        <?php
                        if ($result->num_rows > 0) {
                            // Output each message
                            while ($row = $result->fetch_assoc()) {
                                $message = $row['message'];
                                $location2 = $row['location'];

                                // Check if the location of the message is the same as the session's location
                                $messageClass = ($location == $location2) ? 'sent' : 'received';
                                echo "<div class='message $messageClass'>$message</div>";
                            }
                        } else {
                            echo "<div class='message received'>No messages yet.</div>";
                        }
                        ?>
                    </div>

                    <!-- Form to send a message -->
                    <form action="" method="POST" class="chat-input">
                        <input type="text" id="chat-input" name="message" placeholder="Type your message..." required />
                        <input type="hidden" name="location2" value="<?php echo $location; ?>" />
                        <!-- Pass session location -->
                        <button type="submit">➤</button>
                    </form>
                </div>
                <div class="excel-download">
                    <div class="excel-header">Excel Data</div>
                    <form method="post" action="" style="padding:10px;">
                        <div class="form-group">
                            <label for="from_date">From Date: </label>
                            <input type="date" class="custom-input" name="from_date" id="from_date" required
                                value="<?= htmlspecialchars($from_date) ?>">
                        </div>
                        <div class="form-group">
                            <label for="to_date">To Date: </label>
                            <input type="date" name="to_date" id="to_date" class="custom-input" required
                                value="<?= htmlspecialchars($to_date) ?>">
                        </div>
                        <button type="submit" name="excel" class="custom-button">Download Excel</button>

                    </form>
                    <table style="display:none">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Ref1</th>
                                <th>Consignor Name</th>
                                <th>Consignor Phone</th>
                                <th>Consignor Email</th>
                                <th>Consignor GSTIN</th>
                                <th>Consignor PAN</th>
                                <th>Consignor Address</th>
                                <th>Consignor District</th>
                                <th>Consignor State</th>
                                <th>Consignor Pincode</th>
                                <th>Consignee Name</th>
                                <th>Consignee Phone</th>
                                <th>Consignee Email</th>
                                <th>Consignee GSTIN</th>
                                <th>Consignee PAN</th>
                                <th>Consignee Address</th>
                                <th>Consignee District</th>
                                <th>Consignee State</th>
                                <th>Consignee Pincode</th>
                                <th>No of Articles</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                                <th>Ewaybill No</th>
                                <th>Said to Contain</th>
                                <th>Actual Weight</th>
                                <th>Charged Weight</th>
                                <th>Goods Value</th>
                                <th>Value Sep</th>
                                <th>Basic Freight</th>
                                <th>Document Charge</th>
                                <th>Other Charge</th>
                                <th>Fuel Surcharge</th>
                                <th>Handling Charge</th>
                                <th>Door Collection</th>
                                <th>Door Delivery</th>
                                <th>Total Freight</th>
                                <th>GST Amount</th>
                                <th>Grand Total</th>
                                <th>Apply GST</th>
                                <th>Date Time</th>
                                <th>Payment Mode</th>
                                <th>Created By</th>
                                <th>Bill Date</th>
                                <th>Transit Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($result)): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['billid']) ?></td>
                                        <td><?= htmlspecialchars($row['ref1']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_name']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_phone']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_email']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_gstin']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_panin']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_address']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_district']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_state']) ?></td>
                                        <td><?= htmlspecialchars($row['consignor_pincode']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_name']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_phone']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_email']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_gstin']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_panin']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_address']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_district']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_state']) ?></td>
                                        <td><?= htmlspecialchars($row['consignee_pincode']) ?></td>
                                        <td><?= htmlspecialchars($row['no_of_articles']) ?></td>
                                        <td><?= htmlspecialchars($row['invoice_no']) ?></td>
                                        <td><?= htmlspecialchars($row['invoice_date']) ?></td>
                                        <td><?= htmlspecialchars($row['ewaybill_no']) ?></td>
                                        <td><?= htmlspecialchars($row['said_to_contain']) ?></td>
                                        <td><?= htmlspecialchars($row['actual_weight']) ?></td>
                                        <td><?= htmlspecialchars($row['charged_weight']) ?></td>
                                        <td><?= htmlspecialchars($row['goods_value']) ?></td>
                                        <td><?= htmlspecialchars($row['value_sep']) ?></td>
                                        <td><?= htmlspecialchars($row['basic_freight']) ?></td>
                                        <td><?= htmlspecialchars($row['document_charge']) ?></td>
                                        <td><?= htmlspecialchars($row['other_charge']) ?></td>
                                        <td><?= htmlspecialchars($row['fuel_surcharge']) ?></td>
                                        <td><?= htmlspecialchars($row['handling_charge']) ?></td>
                                        <td><?= htmlspecialchars($row['door_collection']) ?></td>
                                        <td><?= htmlspecialchars($row['door_delivery']) ?></td>
                                        <td><?= htmlspecialchars($row['total_freight']) ?></td>
                                        <td><?= htmlspecialchars($row['gst_amount']) ?></td>
                                        <td><?= htmlspecialchars($row['grand_total']) ?></td>
                                        <td><?= htmlspecialchars($row['apply_gst']) ?></td>
                                        <td><?= htmlspecialchars($row['date_time']) ?></td>
                                        <td><?= htmlspecialchars($row['paymentMode']) ?></td>
                                        <td><?= htmlspecialchars($row['created_by']) ?></td>
                                        <td><?= htmlspecialchars($row['billdate']) ?></td>
                                        <td><?= htmlspecialchars($row['transit_status']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <!-- MAIN -->
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
                0
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
        document.addEventListener("DOMContentLoaded", function () {
            const currentMonthElement = document.getElementById("current-month");
            const calendarElement = document.getElementById("calendar");
            const todoInput = document.getElementById("todo-input");
            const addTodoButton = document.getElementById("add-todo");
            const todoList = document.getElementById("todo-list");

            let selectedDate = new Date().toISOString().split('T')[0]; // Format as YYYY-MM-DD
            let currentDate = new Date();

            function updateCalendar() {
                const month = currentDate.getMonth();
                const year = currentDate.getFullYear();
                const firstDayOfMonth = new Date(year, month, 1);
                const lastDayOfMonth = new Date(year, month + 1, 0);
                const daysInMonth = lastDayOfMonth.getDate();
                const startDay = firstDayOfMonth.getDay();

                currentMonthElement.innerText = `${firstDayOfMonth.toLocaleString('default', { month: 'long' })} ${year}`;
                calendarElement.innerHTML = ""; // Clear previous days

                for (let i = 0; i < startDay; i++) {
                    const emptyDiv = document.createElement("div");
                    emptyDiv.classList.add("empty-day");
                    calendarElement.appendChild(emptyDiv);
                }

                for (let i = 1; i <= daysInMonth; i++) {
                    const day = new Date(year, month, i);
                    const dayElement = document.createElement("div");
                    dayElement.innerText = i;
                    dayElement.classList.add("calendar-day");

                    if (day.toISOString().split('T')[0] === selectedDate) {
                        dayElement.classList.add("active");
                    }

                    dayElement.onclick = () => {
                        selectedDate = day.toISOString().split('T')[0]; // Set selected date
                        fetchTasks(); // Fetch tasks for the selected date
                        highlightSelectedDay(dayElement);
                    };

                    calendarElement.appendChild(dayElement);
                }
            }

            function highlightSelectedDay(dayElement) {
                document.querySelectorAll(".calendar-day").forEach(day => {
                    day.classList.remove("active");
                });
                dayElement.classList.add("active");
            }

            function fetchTasks() {
                console.log('Fetching tasks for date:', selectedDate);  // Debugging line
                fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `selectedDate=${selectedDate}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error("Error:", data.error);
                            return;
                        }
                        localStorage.setItem("tasks", JSON.stringify(data)); // Store tasks with ID
                        renderTasks();
                    })
                    .catch(error => console.error("Error fetching tasks:", error));
            }

            function renderTasks() {
                const tasks = JSON.parse(localStorage.getItem("tasks")) || [];
                todoList.innerHTML = "";

                if (tasks.length === 0) {
                    todoList.innerHTML = "<li>No tasks for this day.</li>";
                } else {
                    tasks.forEach(task => {
                        const taskItem = document.createElement("li");
                        taskItem.innerHTML = `${task.task}
                            <div class="icons">
                                 <span onclick="editTask(${task.id}, '${task.task}')">✏️</span>
                    <span onclick="deleteTask(${task.id})">❌</span>
                            </div>`;
                        todoList.appendChild(taskItem);
                    });
                }
            }

            function addNewTask() {
                const task = todoInput.value.trim();
                if (!task) return;

                console.log("Sending task:", task, selectedDate); // Debugging

                fetch("inserttodo.php", {
                    method: "POST", // 🚀 Sends data instead of retrieving
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `task=${encodeURIComponent(task)}&selectedDate=${selectedDate}`
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Response received:", data);
                        if (data.success && data.id) {
                            let tasks = JSON.parse(localStorage.getItem("tasks")) || [];
                            tasks.push({ id: data.id, task: task });
                            localStorage.setItem("tasks", JSON.stringify(tasks));
                            renderTasks();
                        }
                    })
                    .catch(error => console.error("Error adding task:", error));

                todoInput.value = "";
            }

            // Function to edit a task
            // Declare editTask globally
            window.editTask = function (taskId, currentTask) {
                console.log('Editing task:', taskId, currentTask); // Debugging line

                // Show prompt for the user to edit the task
                const updatedTask = prompt("Edit your task:", currentTask);
                if (updatedTask === null || updatedTask.trim() === "") return; // If cancelled or empty, do nothing

                console.log('Saving updated task:', updatedTask); // Debugging line

                // Make an API request to update the task in the database
                fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `updateTask=${encodeURIComponent(updatedTask)}&taskId=${taskId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data)
                        if (data.success) {
                            console.log("Task updated successfully:", data);
                            fetchTasks(); // Reload tasks to reflect changes
                        } else {
                            console.error("Failed to update task:", data.error);
                            alert("Error updating task. Please try again.");
                        }
                    })
                    .catch(error => {
                        console.error("Error updating task:", error);
                        alert("An error occurred. Please check your connection and try again.");
                    });
            };
            window.deleteTask = function (taskId) {
                // Show confirmation prompt before deleting
                const confirmDelete = confirm("Are you sure you want to delete this task?");
                if (!confirmDelete) return; // Exit if the user cancels

                console.log("Deleting task:", taskId); // Debugging log

                // Make an API request to delete the task
                fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `deleteTaskId=${taskId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log("Task deleted successfully:", taskId);
                            fetchTasks(); // Reload tasks to reflect deletion
                        } else {
                            console.error("Failed to delete task:", data.error);
                            alert("Error deleting task. Please try again.");
                        }
                    })
                    .catch(error => {
                        console.error("Error deleting task:", error);
                        alert("An error occurred. Please check your connection and try again.");
                    });
            };

            updateCalendar();
            fetchTasks();

            document.getElementById("prev-month").addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                updateCalendar();
            });

            document.getElementById("next-month").addEventListener("click", () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                updateCalendar();
            });

            addTodoButton.addEventListener("click", addNewTask); // Add new task when button is clicked
        });

        let isUserInteracting = false;
        let isMessageFieldEmpty = true;
        let idleTimeout = null; // Initialize variable
        let isUserScrolling = false;

        // Function to detect user interaction and reset timeout
        function detectUserInteraction() {
            isUserInteracting = true;
            if (idleTimeout) clearTimeout(idleTimeout); // Clear previous timeout

            idleTimeout = setTimeout(() => {
                isUserInteracting = false;
            }, 3000); // Reset interaction flag after 3 seconds of inactivity
        }

        // Detect keypress in the chat input field
        const chatInput = document.getElementById('chat-input');
        const todoinput = document.getElementById('todo-input');
        const fromdate = document.getElementById('from_date');
        const todate = document.getElementById('to_date');
        if (chatInput && todoinput && fromdate && todate) {
            chatInput.addEventListener('keyup', function () {
                isMessageFieldEmpty = this.value.trim() === "";
                detectUserInteraction();
            });
            todoinput.addEventListener('keyup', function () {
                isMessageFieldEmpty = this.value.trim() === "";
                detectUserInteraction();
            });
            fromdate.addEventListener('change', function () {
                isMessageFieldEmpty = this.value.trim() === "";
                detectUserInteraction();
            });
            todate.addEventListener('change', function () {
                isMessageFieldEmpty = this.value.trim() === "";
                detectUserInteraction();
            });

        }

        // Detect mouse movement or keypress
        document.addEventListener('mousemove', detectUserInteraction);
        document.addEventListener('keydown', detectUserInteraction);

        // Ensure auto-reload continues working after switching tabs
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && !isUserInteracting && isMessageFieldEmpty) {
                location.reload();
            }
        });

        // Automatically reload page every 4 seconds if no interaction and input is empty
        setInterval(function () {
            if (!isUserInteracting && isMessageFieldEmpty && !document.hidden && document.getElementById("settings-modal").style.display == "none") {
                location.reload();
            }
        }, 4000);

        // Scroll to bottom logic
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            // Detect when the user scrolls
            chatMessages.addEventListener('scroll', function () {
                isUserScrolling = chatMessages.scrollTop < (chatMessages.scrollHeight - chatMessages.clientHeight - 10);
            });

            // Scroll to the bottom on page load, unless the user is scrolling
            window.onload = function () {
                if (!isUserScrolling) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            };
        }


    </script>
</body>

</html>