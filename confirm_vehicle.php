<?php
$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vrid = $_POST['vrid'];

    $query = "SELECT docket_no FROM vehicle_details WHERE id = '$vrid'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $allSuccess = true;

        while ($row = $result->fetch_assoc()) {
            $docketNos = $row['docket_no'];
            $docketNoArray = explode(',', $docketNos);

            foreach ($docketNoArray as $docketNo) {
                $docketNo = trim($docketNo);
                $updateQuery = "UPDATE invoices SET transit_status = 'Received' WHERE billid = '$docketNo'";
                if ($conn->query($updateQuery) !== TRUE) {
                    $allSuccess = false;
                    echo "<div class='alert alert-danger mt-3'>Error updating status for docket $docketNo: " . $conn->error . "</div>";
                }
            }
        }

        $updateVehicleQuery = "UPDATE vehicle_details SET status = 'Received' WHERE id = '$vrid'";
        if ($conn->query($updateVehicleQuery) !== TRUE) {
            $allSuccess = false;
            echo "<div class='alert alert-danger mt-3'>Error updating vehicle " . $conn->error . "</div>";
        }

        if ($allSuccess) {
            echo "<script>alert('Invoice Confirmed successfully.');  window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invoice Confirmed Failed.');  window.history.back();</script>";

    }
}
?>