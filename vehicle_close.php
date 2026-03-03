<?php
$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehicleNumber = $_POST['vehicle_number'];

    // Step 1: Get docket_no from vehicle_details where status = 'Moved'
    $stmt = $conn->prepare("SELECT docket_no FROM vehicle_details WHERE vehicle_number = ? AND status = 'Moved'");
    $stmt->bind_param("s", $vehicleNumber);
    $stmt->execute();
    $stmt->bind_result($docketNoStr);

    if ($stmt->fetch()) {
        $stmt->close();

        $docketNos = array_map('trim', explode(',', $docketNoStr));
        $allSuccess = true;

        // Step 2: Update invoices
        $updateInvoice = $conn->prepare("UPDATE invoices SET transit_status = 'Mailed' WHERE billid = ?");
        foreach ($docketNos as $billid) {
            $updateInvoice->bind_param("s", $billid);
            if (!$updateInvoice->execute()) {
                $allSuccess = false;
                echo "<div class='alert alert-danger'>Failed to cancel billid: $billid</div>";
            }
        }
        $updateInvoice->close();

        // Step 3: Update vehicle_details status to Cancelled
        $updateVehicle = $conn->prepare("UPDATE vehicle_details SET status = 'Cancelled' WHERE vehicle_number = ? AND status = 'Moved'");
        $updateVehicle->bind_param("s", $vehicleNumber);
        if (!$updateVehicle->execute()) {
            $allSuccess = false;
            echo "<div class='alert alert-danger'>Failed to cancel vehicle status for: $vehicleNumber</div>";
        }
        $updateVehicle->close();

        if ($allSuccess) {
            echo "<script>alert('All vehicle status have been marked as Cancelled.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No Moved vehicle found with this number.'); window.history.back();</script>";
        $stmt->close();
    }
}
?>