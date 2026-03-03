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
require './dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['query']) && isset($_POST['field'])) {
    $query = $_POST['query'];
    $sql = "SELECT * FROM client WHERE name LIKE '%$query%' OR address LIKE '%$query%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<ul class="list-group">';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list-group-item suggestion-item" data-details=\'' . json_encode($row) . '\'>';
            echo $row['name'] . ' - ' . $row['address'];
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<ul class="list-group"><li class="list-group-item">No results found</li></ul>';
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Assign POST variables to clean variables
    $consignor_name = $_POST['consignor_name'] ?? '';
    $consignor_phone = $_POST['consignor_phone'] ?? '';
    $consignor_email = $_POST['consignor_email'] ?? '';
    $consignor_gstin = $_POST['consignor_gstin'] ?? '';
    $consignor_address = $_POST['consignor_address'] ?? '';
    $consignor_district = $_POST['consignor_district'] ?? '';
    $consignor_state = $_POST['consignor_state'] ?? '';
    $consignor_pincode = $_POST['consignor_pincode'] ?? '';
    $consignor_panin = $_POST['consignor_panin'] ?? '';
    $consignee_name = $_POST['consignee_name'] ?? '';
    $consignee_phone = $_POST['consignee_phone'] ?? '';
    $consignee_email = $_POST['consignee_email'] ?? '';
    $consignee_gstin = $_POST['consignee_gstin'] ?? '';
    $consignee_address = $_POST['consignee_address'] ?? '';
    $consignee_district = $_POST['consignee_district'] ?? '';
    $consignee_state = $_POST['consignee_state'] ?? '';
    $consignee_pincode = $_POST['consignee_pincode'] ?? '';
    $consignee_panin = $_POST['consignee_panin'] ?? '';
    $invoice_no = $_POST['invoice_no'] ?? '';
    $ewaybill_no = $_POST['ewaybill_no'] ?? '';
    $no_of_articles = $_POST['no_of_articles'] ?? 0;
    $said_to_contain = $_POST['said_to_contain'] ?? '';
    $actual_weight = $_POST['actual_weight'] ?? 0;
    $charged_weight = $_POST['charged_weight'] ?? 0;
    $goods_value = $_POST['goods_value'] ?? '0';
    $value_sep = $_POST['value_sep'] ?? '';
    $basic_freight = $_POST['basic_freight'] ?? 0;
    $document_charge = $_POST['document_charge'] ?? 0;
    $other_charge = $_POST['other_charge'] ?? 0;
    $fuel_surcharge = $_POST['fuel_surcharge'] ?? 0;
    $handling_charge = $_POST['handling_charge'] ?? 0;
    $door_collection = $_POST['door_collection'] ?? 0;
    $door_delivery = $_POST['door_delivery'] ?? 0;
    $total_freight = $_POST['total_freight'] ?? 0;
    $gst_amount = $_POST['gst_amount'] ?? 0;
    $paymentMode = $_POST['paymentMode'] ?? '';
    $grand_total = $_POST['grand_total'] ?? 0;
    $dateInput = $_POST['date'];
    $timestamp = strtotime($dateInput);
    // Convert apply_gst "Yes" or "No" to Boolean value
    $apply_gst = isset($_POST['apply_gst']) ? 'Yes' : 'No';
    $fast = isset($_POST['fast']) ? 'Yes' : 'No';

    $status = 'Not_initiated';
    $result = $conn->query("SELECT MAX(billid) AS max_bill FROM invoices");
    $row = $result->fetch_assoc();

    // Extract the number and increment
    if ($row['max_bill']) {
        // Extract the numeric part after the first 6 characters
        $num = (int) substr($row['max_bill'], 6);
        // Generate new bill ID with padding
        $new_billid = 'SV2526' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
    } else {
        // If no existing bill, start from SV252600001
        $new_billid = 'SV252600001';
    }

    $billdate = $_POST['date'];
    $tod = date('d-M-Y h:iA', $timestamp);
    $tod2 = date('Y-m-d', $timestamp);
    $timezone = new DateTimeZone('Asia/Kolkata');
    // Get the current time in that timezone
    $currentTime = new DateTime('now', $timezone);

    // Store the formatted time in a variable called $formattedTime
    $formattedTime = $currentTime->format('h:iA');
    // Insert query with prepared statement
    $stmt = $conn->prepare("INSERT INTO invoices (
        billid, ref1, consignor_name, consignor_phone, consignor_email, consignor_gstin, consignor_panin, 
        consignor_address, consignor_district, consignor_state, consignor_pincode, consignee_name, consignee_phone, 
        consignee_email, consignee_gstin, consignee_panin, consignee_address, consignee_district, consignee_state, 
        consignee_pincode, no_of_articles, invoice_no, invoice_date, ewaybill_no, said_to_contain, actual_weight, 
        charged_weight, goods_value, value_sep, basic_freight, document_charge, other_charge, fuel_surcharge, 
        handling_charge, door_collection, door_delivery, total_freight, gst_amount, grand_total, apply_gst, 
        date_time, paymentMode, created_by, billdate, transit_status,billtime,fast
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?
    )");

    // Bind parameters
    $stmt->bind_param(
        "ssssssssssssssssssssssssssissssdddddiddssssssss",
        $new_billid,
        $_POST['ref1'],
        $consignor_name,
        $consignor_phone,
        $consignor_email,
        $consignor_gstin,
        $consignor_panin,
        $consignor_address,
        $consignor_district,
        $consignor_state,
        $consignor_pincode,
        $consignee_name,
        $consignee_phone,
        $consignee_email,
        $consignee_gstin,
        $consignee_panin,
        $consignee_address,
        $consignee_district,
        $consignee_state,
        $consignee_pincode,
        $no_of_articles,
        $invoice_no,
        $_POST['invoice_date'],
        $ewaybill_no,
        $said_to_contain,
        $actual_weight,
        $charged_weight,
        $goods_value,
        $value_sep,
        $basic_freight,
        $document_charge,
        $other_charge,
        $fuel_surcharge,
        $handling_charge,
        $door_collection,
        $door_delivery,
        $total_freight,
        $gst_amount,
        $grand_total,
        $apply_gst,
        $tod,
        $paymentMode,
        $location,
        $billdate,
        $status,
        $formattedTime,
        $fast
    );


    // Execute the statement
    function convertImageToBase64($filePath)
    {
        if (!file_exists($filePath)) {
            return ''; // Return empty if file does not exist
        }

        $imageData = file_get_contents($filePath);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    if ($stmt->execute()) {
        $districts = [$consignor_district, $consignee_district];

        foreach ($districts as $district) {
            $formattedDistrict = ucwords(strtolower(str_replace('_', ' ', trim($district))));

            if ($formattedDistrict !== '') {
                $stmt = $conn->prepare("SELECT id FROM districts WHERE LOWER(name) = LOWER(?)");
                $stmt->bind_param("s", $formattedDistrict);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $insert = $conn->prepare("INSERT INTO districts (name) VALUES (?)");
                    $insert->bind_param("s", $formattedDistrict);
                    $insert->execute();
                }
            }
        }
        $states = [$consignor_state, $consignee_state];

        foreach ($states as $state) {
            $formattedState = ucwords(strtolower(str_replace('_', ' ', trim($state))));

            if ($formattedState !== '') {
                $stmt = $conn->prepare("SELECT id FROM states WHERE LOWER(name) = LOWER(?)");
                $stmt->bind_param("s", $formattedState);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $insert = $conn->prepare("INSERT INTO states (name) VALUES (?)");
                    $insert->bind_param("s", $formattedState);
                    $insert->execute();
                }
            }
        }
        $said_to_contain = trim($_POST['said_to_contain']);
        $formattedItem = ucwords(strtolower($said_to_contain));

        if ($formattedItem !== '') {
            $stmt = $conn->prepare("SELECT id FROM items WHERE LOWER(name) = LOWER(?)");
            $stmt->bind_param("s", $formattedItem);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $insert = $conn->prepare("INSERT INTO items (name) VALUES (?)");
                $insert->bind_param("s", $formattedItem);
                $insert->execute();
            }
        }
        list($date_part, $time_part) = explode(" ", $tod);
        // Initialize Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $logoPath = __DIR__ . "/Asset/Logo.jpg";
        $base64Image = convertImageToBase64($logoPath);
        $length = strlen($consignor_address);
        $fontSize = "8px";
        $length2 = strlen($consignee_address);
        $fontSize2 = "8px";

        if ($length > 50) {
            $fontSize = "7px";
        }
        if ($length > 80) {
            $fontSize = "6px";
        }
        if ($length2 > 50) {
            $fontSize2 = "7px";
        }
        if ($length2 > 80) {
            $fontSize2 = "6px";
        }
        $fstatus = "-";
        if ($fast == "Yes") {
            $fstatus = "FTL";
        }


        // HTML Content for PDF
        $html = '<!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <style>
                table {
                    margin:0px auto;
                    border-collapse: collapse;
                    width: 100%;
                    font-size: 8px;
                    table-layout: fixed;
            
        }
                    h3{
                    margin:20px 0px 5px 0px;
                    text-align:center;
                    }
                td {
                    height: 15px;
                    width: 89px;
                    text-align: left;
                    border-collapse: collapse;
                    padding-left:4px;
                }

                .border-top {
                    border-top: 1px solid;
                }

                .border-right {
                    border-right: 1px solid;
                }

                .border-bottom {
                    border-bottom: 1px solid;
                }

                .border-left {
                    border-left: 1px solid;
                }

                .border-top-right {
                    border-top: 1px solid;
                    border-right: 1px solid;
                }

                .border-top-left {
                    border-top: 1px solid;
                    border-left: 1px solid;
                }

                .border-bottom-right {
                    border-bottom: 1px solid;
                    border-right: 1px solid;
                }

                .border-bottom-left {
                    border-bottom: 1px solid;
                    border-left: 1px solid;
                }

                /* Full Border */
                .border-all {
                    border: 1px solid;
                }
            </style>
        </head>

        <body>
        <h3>Consignor Copy</h3>
            <table border="0">
                <tr>
                

                    <td colspan="3" class="border-top border-left">Websitelink</td>
        <td colspan="5" rowspan="5" class="border-all" style="text-align:center;"><img src="' . $base64Image . '" style=" height: 77px;
                    width: 118px;">
            </td>
                    <td class="border-top-left">GCN</td>
                    <td colspan="3" class="border-top-right">' . $new_billid . ' </td>
                </tr>
                <tr>
                    <td colspan="3" class="border-left">Gstin:33EUOPK3413F1ZS</td>
                    <td class="border-left">Date</td>
                    <td>' . $billdate . '</td>
                    <td>Time</td>
                    <td class="border-right">' . $formattedTime . '</td>
                </tr>
                <tr>

                    <td colspan="5" class="border-right border-left">Phone:+91 9585156857</td>

                    <td class="border-left">From</td>
                    <td colspan="3" class="border-right">' . $consignor_district . '</td>

                </tr>
                <tr>
        <td colspan="5" class="border-right border-left">Whatsappno:9585156817</td>

                    <td class="border-left">To</td>
                    <td colspan="3" class="border-right">' . $consignee_district . '</td>

                </tr>
                <tr>

                    <td colspan="5" class="border-right border-bottom border-left">Mailid:svlogistics.sales@gmail.com</td>

                    <td colspan="1" class="border-bottom" style="text-align: left;font-size:7px;">Booking Mode</td>
                    <td colspan="1" class="border-bottom">' . $paymentMode . '</td>
                    <td colspan="1" class="border-bottom" style="text-align: left;">Type</td>
                    <td colspan="1" class="border-bottom-right">' . $fstatus . '</td>

                </tr>
                <tr>
                    <td colspan="4" class="border-right border-left">Consignor Details</td>

                    <td colspan="4" class="border-bottom-right">Invoice Number</td>

                    <td colspan="2" class="border-bottom-right">Freight</td>

                    <td colspan="2" class="border-bottom-right">Amount</td>

                </tr>
                <tr>
                    <td class="border-left">Name</td>
                    <td colspan="3" class="border-right">' . $consignor_name . '</td>

                    <td rowspan="4" colspan="4" class="border-bottom-right"style="vertical-align: top; text-align: left;">' . $invoice_no . '</td>

                    <td colspan="2" class="border-bottom-right">Basic</td>

                    <td colspan="2" class="border-bottom-right">' . $basic_freight . '</td>
                </tr>
                <tr>
                    <td rowspan="2" class="border-left">Address</td>
            <td rowspan="2" colspan="3" class="border-right" style="font-size:' . $fontSize . '">' . $consignor_address . '</td>
                    <td colspan="2" class="border-bottom-right">Document</td>

                    <td colspan="2" class="border-bottom-right">' . $document_charge . '</td>
                </tr>
                <tr>




                    <td colspan="2" class="border-bottom-right">Other</td>

                    <td colspan="2" class="border-bottom-right">' . $other_charge . '</td>
                </tr>
                <tr>

                    <td class="border-left">District</td>
                    <td>' . $consignor_district . '</td>
                    <td>Pincode</td>
                    <td class="border-right">' . $consignor_pincode . '</td>
                    <td colspan="2" class="border-bottom-right">Door Collection</td>

                    <td colspan="2" class="border-bottom-right">' . $door_collection . '</td>
                </tr>
                <tr>
                    <td class="border-left">Gstin</td>
                    <td>' . $consignor_gstin . '</td>
                    <td style="text-align:right">Pan</td>
                    <td class="border-right">' . $consignor_panin . '</td>
                    <td colspan="4" class="border-all">E-Waybill</td>

                    <td colspan="2" class="border-bottom-right">Door Delivery</td>

                    <td colspan="2" class="border-bottom-right">' . $door_delivery . '</td>
                </tr>
                <tr>
                    <td class="border-left">Mobile</td>
                    <td colspan="3" class="border-right">' . $consignor_phone . '</td>

                    <td colspan="4" rowspan="4" class="border-bottom-right" style="vertical-align: top; text-align: left;">' . $ewaybill_no . '</td>
                    <td colspan="2" class="border-bottom-right">fuel_surcharge</td>

                    <td colspan="2" class="border-bottom-right">' . $fuel_surcharge . '</td>
                </tr>
                <tr>
                    <td class="border-bottom-left">Email</td>
                    <td colspan="3" class="border-bottom-right">' . $consignor_email . '</td>


                    <td colspan="2" class="border-bottom-right">handling_charge</td>

                    <td colspan="2" class="border-bottom-right">' . $handling_charge . '</td>
                </tr>
                <tr>
                    <td colspan="4" class="border-right border-left">Consignee Details</td>


                    <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td class="border-left">Name</td>
                    <td colspan="3" class="border-right">' . $consignee_name . '</td>
                <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td rowspan="2" class="border-left">Address</td>
                    <td rowspan="2" colspan="3" class="border-right" style="font-size:' . $fontSize2 . '">' . $consignee_address . '</td>

                    <td colspan="2">Values</td>

                    <td colspan="2" class="border-right">' . $goods_value . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>
                </tr>
                <tr>



                    <td colspan="2">Description</td>

                    <td colspan="2" class="border-right">' . $said_to_contain . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td class="border-left">District</td>
                    <td>' . $consignee_district . '</td>
                    <td>Pincode</td>
                    <td class="border-right">' . $consignee_pincode . '</td>
                    <td colspan="2">No of articles</td>
                    <td colspan="2" class="border-right">' . $no_of_articles . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>
                </tr>
                <tr>
                    <td class="border-left">Gstin</td>
                    <td>' . $consignee_gstin . '</td>
                    <td style="text-align:right">Pan</td>
                    <td class="border-right">' . $consignee_panin . '</td>
                    <td colspan="2">Actual Weight</td>
                    <td colspan="2" class="border-right">' . $actual_weight . '</td>
                    <td colspan="2" class="border-top-right">Total Frieght</td>
                    <td colspan="2" class="border-top-right">' . $total_freight . '</td>

                </tr>
                <tr>
                    <td class="border-left">Mobile</td>
                    <td colspan="3" class="border-right">' . $consignee_phone . '</td>

                    <td colspan="2">Charged Weight</td>

                    <td colspan="2" class="border-right">' . $charged_weight . '</td>
                    <td colspan="2" class="border-top-right">GST(CGST 8%+SGST 8%)</td>

                    <td colspan="2" class="border-top-right">' . $gst_amount . '</td>
                </tr>
                <tr>
                    <td class="border-bottom-left">Email</td>
                    <td colspan="3" class="border-bottom-right">' . $consignee_email . '</td>

                    <td class="border-bottom"></td>
                    <td class="border-bottom"></td>
                    <td class="border-bottom"></td>
                    <td class="border-bottom-right"></td>

                    <td colspan="2" class="border-top-right">Grand Total</td>

                    <td colspan="2" class="border-top-right">' . $grand_total . '</td>
                </tr>
                <tr>
                    <td colspan="7" class="border-left">Terms and condition</td>

                

                    <td colspan="5" rowspan="1" class="border-top border-left border-right" style="text-align:center;"></td>


                </tr>
                <tr>
                    <td colspan="7" class="border-left border-right">1.Only Providing Vehicles We Are Not Responsible For Damages During Transit
        .</td>


            <td colspan="5" class="border-right" style="text-align:center;"></td>

                </tr>
                <tr>
                    <td colspan="7" class="border-left border-right">2.We Have Only 2 Own Vehicles So Don’t Deduct TDS Amount
        .</td>

                
        <td colspan="5" class="border-right" style="text-align:center;">Signature<br>(Authorized)</td>

                </tr>
                <tr>
                    <td colspan="7" class="border-left border-bottom-right">3.GST-Revise Mechanism Individual 
        Customer-12% GST Applicable.  </td>
        <td colspan="5" class="border-right border-bottom" style="text-align:center;">Generated by GCN No sign Requird.</td>
                    


                </tr>
                </table>
                <h3>Consignee Copy</h3>
                    <table border="0">
            <tr>
                

                    <td colspan="3" class="border-top border-left">Websitelink</td>
        <td colspan="5" rowspan="5" class="border-all" style="text-align:center;"><img src="' . $base64Image . '" style=" height: 77px;
                    width: 118px;">
            </td>
                    <td class="border-top-left">GCN</td>
                    <td colspan="3" class="border-top-right">' . $new_billid . ' </td>
                </tr>
                <tr>
                    <td colspan="3" class="border-left">Gstin:33EUOPK3413F1ZS</td>
                    <td class="border-left">Date</td>
                    <td>' . $billdate . '</td>
                    <td>Time</td>
                    <td class="border-right">' . $formattedTime . '</td>
                </tr>
                <tr>

                    <td colspan="5" class="border-right border-left">Phone:+91 9585156857</td>

                    <td class="border-left">From</td>
                    <td colspan="3" class="border-right">' . $consignor_district . '</td>

                </tr>
                <tr>
        <td colspan="5" class="border-right border-left">Whatsappno:9585156817</td>

                    <td class="border-left">To</td>
                    <td colspan="3" class="border-right">' . $consignee_district . '</td>

                </tr>
                <tr>

                    <td colspan="5" class="border-right border-bottom border-left">Mailid:svlogistics.sales@gmail.com</td>

                    <td colspan="1" class="border-bottom" style="text-align: left;font-size:7px;">Booking Mode</td>
                    <td colspan="1" class="border-bottom">' . $paymentMode . '</td>
                    <td colspan="1" class="border-bottom" style="text-align: left;">Type</td>
                    <td colspan="1" class="border-bottom-right">' . $fstatus . '</td>

                </tr>
                <tr>
                    <td colspan="4" class="border-right border-left">Consignor Details</td>

                    <td colspan="4" class="border-bottom-right">Invoice Number</td>

                    <td colspan="2" class="border-bottom-right">Freight</td>

                    <td colspan="2" class="border-bottom-right">Amount</td>

                </tr>
                <tr>
                    <td class="border-left">Name</td>
                    <td colspan="3" class="border-right">' . $consignor_name . '</td>

                    <td rowspan="4" colspan="4" class="border-bottom-right" style="vertical-align: top; text-align: left;">' . $invoice_no . '</td>

                    <td colspan="2" class="border-bottom-right">Basic</td>

                    <td colspan="2" class="border-bottom-right">' . $basic_freight . '</td>
                </tr>
                <tr>
                    <td rowspan="2" class="border-left">Address</td>
                    <td rowspan="2" colspan="3" class="border-right" style="font-size:' . $fontSize . '">' . $consignor_address . '</td>


                    <td colspan="2" class="border-bottom-right">Document</td>

                    <td colspan="2" class="border-bottom-right">' . $document_charge . '</td>
                </tr>
                <tr>




                    <td colspan="2" class="border-bottom-right">Other</td>

                    <td colspan="2" class="border-bottom-right">' . $other_charge . '</td>
                </tr>
                <tr>

                    <td class="border-left">District</td>
                    <td>' . $consignor_district . '</td>
                    <td>Pincode</td>
                    <td class="border-right">' . $consignor_pincode . '</td>
                    <td colspan="2" class="border-bottom-right">Door Collection</td>

                    <td colspan="2" class="border-bottom-right">' . $door_collection . '</td>
                </tr>
                <tr>
                    <td class="border-left">Gstin</td>
                    <td>' . $consignor_gstin . '</td>
                    <td style="text-align:right">Pan</td>
                    <td class="border-right">' . $consignor_panin . '</td>
                    <td colspan="4" class="border-all">E-Waybill</td>

                    <td colspan="2" class="border-bottom-right">Door Delivery</td>

                    <td colspan="2" class="border-bottom-right">' . $door_delivery . '</td>
                </tr>
                <tr>
                    <td class="border-left">Mobile</td>
                    <td colspan="3" class="border-right">' . $consignor_phone . '</td>

                    <td colspan="4" rowspan="4" class="border-bottom-right" style="vertical-align: top; text-align: left;">' . $ewaybill_no . '</td>
                <td colspan="2" class="border-bottom-right">fuel_surcharge</td>

                    <td colspan="2" class="border-bottom-right">' . $fuel_surcharge . '</td>
                </tr>
                <tr>
                    <td class="border-bottom-left">Email</td>
                    <td colspan="3" class="border-bottom-right">' . $consignor_email . '</td>


                    <td colspan="2" class="border-bottom-right">handling_charge</td>

                    <td colspan="2" class="border-bottom-right">' . $handling_charge . '</td>
                </tr>
                <tr>
                    <td colspan="4" class="border-right border-left">Consignee Details</td>


                <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td class="border-left">Name</td>
                    <td colspan="3" class="border-right">' . $consignee_name . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td rowspan="2" class="border-left">Address</td>
                    <td rowspan="2" colspan="3" class="border-right" style="font-size:' . $fontSize2 . '">' . $consignee_address . '</td>

                    <td colspan="2">Values</td>

                    <td colspan="2" class="border-right">' . $goods_value . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>
                </tr>
                <tr>



                    <td colspan="2">Description</td>

                    <td colspan="2" class="border-right">' . $said_to_contain . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>

                </tr>
                <tr>
                    <td class="border-left">District</td>
                    <td>' . $consignee_district . '</td>
                    <td>Pincode</td>
                    <td class="border-right">' . $consignee_pincode . '</td>
                    <td colspan="2">No of articles</td>
                    <td colspan="2" class="border-right">' . $no_of_articles . '</td>
        <td  colspan="2" class="border-right border-bottom"></td>
                    <td colspan="2" class="border-right border-left border-bottom"></td>
                </tr>
                <tr>
                    <td class="border-left">Gstin</td>
                    <td>' . $consignee_gstin . '</td>
                    <td style="text-align:right">Pan</td>
                    <td class="border-right">' . $consignee_panin . '</td>
                    <td colspan="2">Actual Weight</td>
                    <td colspan="2" class="border-right">' . $actual_weight . '</td>
                    <td colspan="2" class="border-top-right">Total Frieght</td>
                    <td colspan="2" class="border-top-right">' . $total_freight . '</td>

                </tr>
                <tr>
                    <td class="border-left">Mobile</td>
                    <td colspan="3" class="border-right">' . $consignee_phone . '</td>

                    <td colspan="2">Charged Weight</td>

                    <td colspan="2" class="border-right">' . $charged_weight . '</td>
                    <td colspan="2" class="border-top-right">GST(CGST 8%+SGST 8%)</td>

                    <td colspan="2" class="border-top-right">' . $gst_amount . '</td>
                </tr>
                <tr>
                    <td class="border-bottom-left">Email</td>
                    <td colspan="3" class="border-bottom-right">' . $consignee_email . '</td>

                    <td class="border-bottom"></td>
                    <td class="border-bottom"></td>
                    <td class="border-bottom"></td>
                    <td class="border-bottom-right"></td>

                    <td colspan="2" class="border-top-right">Grand Total</td>

                    <td colspan="2" class="border-top-right">' . $grand_total . '</td>
                </tr>
                
                <tr>
                    <td colspan="7" class="border-left">Terms and condition</td>

                

                    <td colspan="5" rowspan="1" class="border-top border-left border-right" style="text-align:center;"></td>


                </tr>
                <tr>
                    <td colspan="7" class="border-left border-right">1.Only Providing Vehicles We Are Not Responsible For Damages During Transit
        .</td>


            <td colspan="5" class="border-right" style="text-align:center;"></td>

                </tr>
                <tr>
                    <td colspan="7" class="border-left border-right">2.We Have Only 2 Own Vehicles So Don’t Deduct TDS Amount
        .</td>

                
        <td colspan="5" class="border-right" style="text-align:center;">Signature<br>(Authorized)</td>

                </tr>
                <tr>
                    <td colspan="7" class="border-left border-bottom-right">3.GST-Revise Mechanism Individual 
        Customer-12% GST Applicable.  </td>
        <td colspan="5" class="border-right border-bottom" style="text-align:center;">Generated by GCN No sign Requird.</td>
                    


                </tr>
                </table>

        </body>

        </html>
        ';

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Set Paper Size and Orientation (A4, Portrait)
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Define folder path
        $folderPath = "Bills/";

        // Ensure directory exists
        if (!is_dir($folderPath) && !mkdir($folderPath, 0777, true) && !is_dir($folderPath)) {
            die("Failed to create directory: " . $folderPath);
        }

        // Define PDF file path
        $pdfPath = $folderPath . $new_billid . ".pdf";

        // Save the PDF file (Only Save, No Download)
        if (file_put_contents($pdfPath, $dompdf->output()) === false) {
            die("Failed to save PDF to " . $pdfPath);
        }
        echo "<div class='alert alert-success mt-3'>Data inserted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Error: " . $stmt->error . "</div>";
    }
}
// Function to fetch matching consignors/consignees
function getSuggestions($conn, $query)
{
    $sql = "SELECT * FROM invoices WHERE consignor_name LIKE ? OR consignee_name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $likeQuery = "%" . $query . "%";
        $stmt->bind_param("ss", $likeQuery, $likeQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $data;
    } else {
        return [];
    }
}

// Handle AJAX request using the existing $conn connection
if (isset($_GET['query'])) {
    echo json_encode(getSuggestions($conn, $_GET['query']));
    exit;
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
        $target_file = $target_dir . basename(path: $_FILES["profile"]["name"]);
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

        .suggestions-box {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            width: 100%;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            padding: 5px;
        }

        .suggestion-item {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
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
                <a href="#" class="active"><i class="bx bxs-inbox icon "></i> Operation <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown show">
                    <li><a href="Admin_Addbill.php" style="color: #2f3192">Waybills</a></li>
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
            <h3 class="form-title">Create Waybill</h3>
            <form method="post">
                <!-- Consignment Type & Requested By -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="row my-3">
                            <div class="col-md-3">
                                <label for="consignmentType">Waybill Type</label>
                                <select id="consignmentType" class="form-select mt-2" name="paymentMode" required>
                                    <option value="Topay">Topay</option>
                                    <option value="TBB">TBB</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="poNumber">PO Number/Ref 1</label>
                                <input type="text" id="poNumber" class="form-control mt-2" name="ref1" />
                            </div>
                            <div class="col-md-3">
                                <label for="date">Bill Date</label>
                                <input type="date" name="date" class="form-control mt-2"
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Table -->
                    <div class="mb-3">
                        <table class="table table-bordered input-table">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Invoice Date</th>
                                    <th>Eway Bill Number</th>
                                    <th>Invoice Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceTableBody">
                                <tr>
                                    <td><input type="text" id="field1" class="form-control"
                                            placeholder="Enter Invoice Number"></td>
                                    <td><input type="date" id="field4" class="form-control"
                                            placeholder="Enter Invoice Date"></td>
                                    <td><input type="text" id="field2" class="form-control"
                                            placeholder="Enter Eway Bill Number"> <small id="ewaybill-error"
                                            style="color: red;font-size:10px;display:none"></small> </td>
                                    <td><input type="number" id="field3" class="form-control"
                                            placeholder="Enter Invoice Value"></td>
                                    <td><button class="btn btn-primary btn-sm" type="button"
                                            onclick="addData()">Add</button></td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <div id="boxContainer" class="bubble-container"></div>
                                        <input type="hidden" id="hiddenData1" name="invoice_no" required>
                                        <input type="hidden" id="hiddenData2" name="ewaybill_no" required>
                                        <input type="hidden" id="hiddenData3" name="invoice_date" required>
                                        <input type="hidden" id="hiddenTotal" name="goods_value" required>
                                        <input type="hidden" id="hiddenNumericValues" name="value_sep" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Consignor & Consignee Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Consignor</label>
                            <div class="position-relative">
                                <input type="text" class="form-control my-2" placeholder="Company Name"
                                    name="consignor_name" id="consignor_name" autocomplete="off" required
                                    onkeyup="fetchSuggestions(this.value, 'consignor')">
                                <div id="consignor-suggestions" class="suggestions-box"></div>
                            </div>
                            <input type="text" class="form-control my-2" placeholder="Address" name="consignor_address"
                                autocomplete="off" required id="consignor_address" />
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control my-2" placeholder="Pin"
                                        name="consignor_pincode" id="consignor_pincode" autocomplete="off" required />
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignor_district"
                                            name="consignor_district" placeholder="Enter or select district"
                                            autocomplete="off" onfocus="showDistrictDropdown()"
                                            oninput="filterDistricts()" required>

                                        <div id="district-dropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                            <?php
                                            $stmt = $conn->prepare("SELECT name FROM districts ORDER BY name ASC");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $name = htmlspecialchars($row['name']);
                                                echo "<div class='dropdown-item' onclick=\"selectDistrict('$name')\">$name</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignor_state"
                                            name="consignor_state" placeholder="Enter or select state"
                                            autocomplete="off" onfocus="showConsignorStateDropdown()"
                                            oninput="filterConsignorStates()" required>

                                        <div id="consignor-state-dropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                            <?php
                                            $stmt = $conn->prepare("SELECT name FROM states ORDER BY name ASC");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $state = htmlspecialchars($row['name']);
                                                echo "<div class='dropdown-item' onclick=\"selectConsignorState('$state')\">$state</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="Gst Number"
                                        name="consignor_gstin" id="consignor_gstin" autocomplete="off"
                                        oninput="this.value = this.value.toUpperCase();"
                                        pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$" />
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="Pan Number"
                                        name="consignor_panin" id="consignor_panin" autocomplete="off"
                                        oninput="this.value = this.value.toUpperCase();"
                                        pattern="^[A-Z]{5}[0-9]{4}[A-Z]{1}$" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Consignee</label>
                            <div class="position-relative">
                                <input type="text" class="form-control my-2" placeholder="Company Name"
                                    name="consignee_name" id="consignee_name" autocomplete="off" required
                                    onkeyup="fetchSuggestions(this.value, 'consignee')">
                                <div id="consignee-suggestions" class="suggestions-box"></div>
                            </div>
                            <input type="text" class="form-control my-2" placeholder="Address" name="consignee_address"
                                autocomplete="off" id="consignee_address" required />
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control my-2" placeholder="Pin"
                                        name="consignee_pincode" id="consignee_pincode" autocomplete="off" required />
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignee_district"
                                            name="consignee_district" placeholder="Enter or select district"
                                            autocomplete="off" onfocus="showConsigneeDropdown()"
                                            oninput="filterConsigneeDistricts()" required>

                                        <div id="consignee-dropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                            <?php
                                            $stmt = $conn->prepare("SELECT name FROM districts ORDER BY name ASC");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $district = htmlspecialchars($row['name']);
                                                echo "<div class='dropdown-item' onclick=\"selectConsigneeDistrict('$district')\">$district</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignee_state"
                                            name="consignee_state" placeholder="Enter or select state"
                                            autocomplete="off" onfocus="showConsigneeStateDropdown()"
                                            oninput="filterConsigneeStates()" required>

                                        <div id="consignee-state-dropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                            <?php
                                            $stmt = $conn->prepare("SELECT name FROM states ORDER BY name ASC");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $state = htmlspecialchars($row['name']);
                                                echo "<div class='dropdown-item' onclick=\"selectConsigneeState('$state')\">$state</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="GST Number"
                                        name="consignee_gstin" autocomplete="off" id="consignee_gstin"
                                        oninput="this.value = this.value.toUpperCase();"
                                        pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$" />
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="Pan Number"
                                        name="consignee_panin" id="consignee_panin" autocomplete="off"
                                        oninput="this.value = this.value.toUpperCase();"
                                        pattern="^[A-Z]{5}[0-9]{4}[A-Z]{1}$" />
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Phone" name="consignor_phone"
                                    required id="consignor_phone" />
                            </div>
                            <div class="col-md-3">
                                <input type="email" class="form-control" placeholder="Email" name="consignor_email"
                                    id="consignor_email" />
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Phone" name="consignee_phone"
                                    id="consignee_phone" required />
                            </div>
                            <div class="col-md-3">
                                <input type="email" class="form-control" placeholder="Email" name="consignee_email"
                                    id="consignee_email" />
                            </div>
                        </div>
                        <div class="row mb-3 mt-4">
                            <label for="" class="mb-2">Package Details</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control mb-2" placeholder="No of article"
                                    name="no_of_articles" required />
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="actual_weight" name="actual_weight"
                                    required />
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="charged_weight"
                                    name="charged_weight" required />
                            </div>

                            <div class="col-md-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="said_to_contain" name="said_to_contain"
                                        placeholder="Enter or select item" autocomplete="off"
                                        onfocus="showSaidToContainDropdown()" oninput="filterSaidToContain()" required>

                                    <div id="said-to-contain-dropdown" class="dropdown-menu w-100 shadow"
                                        style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                        <?php

                                        $stmt = $conn->prepare("SELECT name FROM items ORDER BY name ASC");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            $item = htmlspecialchars($row['name']);
                                            echo "<div class='dropdown-item' onclick=\"selectSaidToContain('$item')\">$item</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3 g-2">
                            <div class="col-md-3">
                                <label class="form-label">Basic Freight</label>
                                <input type="number" class="form-control" name="basic_freight" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Document Charge</label>
                                <input type="number" class="form-control" name="document_charge" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fuel Surcharge</label>
                                <input type="number" class="form-control" name="fuel_surcharge" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Handling Charge</label>
                                <input type="number" class="form-control" name="handling_charge" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Other Charge</label>
                                <input type="number" class="form-control" name="other_charge" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Door Collection</label>
                                <input type="number" class="form-control" name="door_collection" />
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Door Delivery</label>
                                <input type="number" class="form-control" name="door_delivery" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total Freight</label>
                                <input type="number" class="form-control" name="total_freight" readonly />
                            </div>
                        </div>
                        <div class="mt-4">
                            <input class="form-check-input" type="checkbox" name="apply_gst" id="apply_gst" />
                            <label class="form-check-label" for="apply_gst">Apply GST (12%)</label>
                            <input class="form-check-input ms-3" type="checkbox" name="fast" id="fast" />
                            <label class="form-check-label" for="fast">FTL</label>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <label class="form-label">GST Amount</label>
                                <input type="number" class="form-control" name="gst_amount" readonly />
                                <small class="form-text text-muted">6% CGST + 6% SGST</small>
                            </div>
                            <div class="col-md-4">
                                <input type="hidden" class="form-control " name="cgst_sgst_amount" readonly />
                                <label class="form-label">Grand Total</label>
                                <input type="number" class="form-control" name="grand_total" readonly />
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </div>
                </div>
            </form>



        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function addData() {
            const field1 = document.getElementById("field1").value.trim();
            const field2 = document.getElementById("field2").value.trim();
            const field4 = document.getElementById("field4").value.trim();
            const field3 = parseFloat(document.getElementById("field3").value.trim());
            const field2Input = document.getElementById("field2");
            const errorLabel = document.getElementById("ewaybill-error");

            // Validate E-way Bill Number (12-digit check)
            // Optional E-way Bill Number: validate only if not empty
            if (field2 !== "" && !/^\d{12}$/.test(field2)) {
                field2Input.style.border = "2px solid red";
                errorLabel.textContent = "E-way Bill Number must be exactly 12 digits.";
                errorLabel.style.display = "block";
                return;
            } else {
                field2Input.style.border = "";
                errorLabel.style.display = "none";
                errorLabel.textContent = "";
            }

            if (field1 && !isNaN(field3) && field4) {
                const id = Date.now();
                data1List.push({ id, value: field1 });
                data2List.push({ id, value: field2 });
                data3List.push({ id, value: field4 });
                numericValues.push({ id, value: field3 });
                totalSum += field3;

                const boxContainer = document.getElementById("boxContainer");
                const box = document.createElement("div");
                box.className = "floating-box";
                box.setAttribute("data-id", id);
                box.innerHTML = `
            <span>${field1} - ${field4} - ${field2} - ${field3.toFixed(2)}</span>
            <span class="edit-btn" onclick="editData(${id})">✎</span>
            <span class="remove-btn" onclick="removeData(${id}, ${field3})">×</span>
        `;
                boxContainer.appendChild(box);

                updateHiddenFields();

                document.getElementById("field1").value = "";
                document.getElementById("field2").value = "";
                document.getElementById("field3").value = "";
                document.getElementById("field4").value = "";
            }
        }

        // Live validation for E-way Bill Number field
        document.getElementById("field2").addEventListener("input", function () {
            const errorLabel = document.getElementById("ewaybill-error");
            if (this.value === "" || /^\d{12}$/.test(this.value)) {
                this.style.border = ""; // Reset border if valid
                errorLabel.textContent = ""; // Remove error message
            } else {
                this.style.border = "2px solid red"; // Red border if invalid
                errorLabel.textContent = "E-way Bill Number must be exactly 12 digits.";
            }
        });

        function editData(id) {
            const data1Item = data1List.find(item => item.id === id);
            const data2Item = data2List.find(item => item.id === id);
            const data3Item = data3List.find(item => item.id === id);
            const numericItem = numericValues.find(item => item.id === id);

            if (data1Item && data2Item && numericItem && data3Item) {
                document.getElementById("field1").value = data1Item.value;
                document.getElementById("field2").value = data2Item.value;
                document.getElementById("field3").value = numericItem.value;
                document.getElementById("field4").value = data3Item.value;

                removeData(id, numericItem.value);
            }
        }

        function removeData(id, value) {
            data1List = data1List.filter(item => item.id !== id);
            data2List = data2List.filter(item => item.id !== id);
            data3List = data3List.filter(item => item.id !== id);
            numericValues = numericValues.filter(item => item.id !== id);
            totalSum -= value;

            const box = document.querySelector(`.floating-box[data-id='${id}']`);
            if (box) box.remove();

            updateHiddenFields();
        }

        function updateHiddenFields() {
            event.preventDefault();
            document.getElementById("hiddenData1").value = data1List.map(item => item.value).join(",");
            document.getElementById("hiddenData2").value = data2List.map(item => item.value).join(",");
            document.getElementById("hiddenData3").value = data3List.map(item => item.value).join(",");
            document.getElementById("hiddenTotal").value = totalSum.toFixed(2);
            document.getElementById("hiddenNumericValues").value = numericValues.map(item => item.value).join(",");
        }
    </script>
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
        document.getElementById("consignee_gstin").addEventListener("input", function () {
            let gstInput = this.value;
            let panInput = document.getElementById("consignee_panin");

            // GSTIN pattern validation
            let gstPattern = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/;

            if (gstPattern.test(gstInput)) {
                // Extract PAN from GST (characters 3 to 12)
                let panExtracted = gstInput.substring(2, 12);
                panInput.value = panExtracted;
            } else {
                panInput.value = ""; // Clear PAN field if GSTIN is invalid
            }
        });
        document.getElementById("consignor_gstin").addEventListener("input", function () {
            let gstInput = this.value;
            let panInput = document.getElementById("consignor_panin");

            // GSTIN pattern validation
            let gstPattern = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/;

            if (gstPattern.test(gstInput)) {
                // Extract PAN from GST (characters 3 to 12)
                let panExtracted = gstInput.substring(2, 12);
                panInput.value = panExtracted;
            } else {
                panInput.value = ""; // Clear PAN field if GSTIN is invalid
            }
        });
        function fetchSuggestions(query, type) {
            const suggestionsBox = document.getElementById(type + "-suggestions");

            if (!query.trim()) {
                suggestionsBox.innerHTML = "";
                suggestionsBox.style.display = "none";
                return;
            }

            fetch("Admin_Addbill.php?query=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = "";

                    const filtered = data.filter(item => {
                        const consName = (item.consignor_name || "").toLowerCase();
                        const cneeName = (item.consignee_name || "").toLowerCase();
                        return consName.includes(query.toLowerCase()) || cneeName.includes(query.toLowerCase());
                    });

                    const seen = new Set();
                    const uniqueData = filtered.filter(item => {
                        const matchSide = (item.consignor_name || "").toLowerCase().includes(query.toLowerCase())
                            ? "consignor" : "consignee";

                        const key = [
                            matchSide === "consignor" ? item.consignor_name : item.consignee_name,
                            matchSide === "consignor" ? item.consignor_address : item.consignee_address,
                            matchSide === "consignor" ? item.consignor_email : item.consignee_email
                        ].join("|").toLowerCase();

                        if (seen.has(key)) return false;
                        seen.add(key);
                        return true;
                    });

                    uniqueData.forEach(item => {
                        const matchSide = (item.consignor_name || "").toLowerCase().includes(query.toLowerCase())
                            ? "consignor" : "consignee";

                        const name = matchSide === "consignor" ? item.consignor_name : item.consignee_name;
                        const address = matchSide === "consignor" ? item.consignor_address : item.consignee_address;
                        const email = matchSide === "consignor" ? item.consignor_email : item.consignee_email;
                        const gst = matchSide === "consignor" ? item.consignor_gstin : item.consignee_gstin;
                        const pan = matchSide === "consignor" ? item.consignor_panin : item.consignee_panin;

                        const suggestionItem = document.createElement("div");
                        suggestionItem.classList.add("suggestion-item");
                        suggestionItem.innerHTML = `
                    <strong>${name}</strong><br>
                    <small>${address}</small><br>
                    <small>${email}</small>
                    <small>🧾 GSTIN: ${gst || 'N/A'}</small><br>
        <small>🪪 PAN: ${pan || 'N/A'}</small>
                `;

                        suggestionItem.onclick = () => {
                            fillFormFields(item, type, matchSide);
                            suggestionsBox.style.display = "none";
                        };

                        suggestionsBox.appendChild(suggestionItem);
                    });

                    suggestionsBox.style.display = "block";
                })
                .catch(error => {
                    console.error("Error fetching suggestions:", error);
                    suggestionsBox.innerHTML = "";
                    suggestionsBox.style.display = "none";
                });
        }


        function fillFormFields(data, inputType, matchSide) {
            const get = (consField, cneeField) =>
                matchSide === "consignor" ? data[consField] : data[cneeField];

            document.getElementById(`${inputType}_name`).value = get("consignor_name", "consignee_name") || "";
            document.getElementById(`${inputType}_address`).value = get("consignor_address", "consignee_address") || "";
            document.getElementById(`${inputType}_pincode`).value = get("consignor_pincode", "consignee_pincode") || "";
            document.getElementById(`${inputType}_phone`).value = get("consignor_phone", "consignee_phone") || "";
            document.getElementById(`${inputType}_email`).value = get("consignor_email", "consignee_email") || "";
            document.getElementById(`${inputType}_gstin`).value = get("consignor_gstin", "consignee_gstin") || "";
            document.getElementById(`${inputType}_panin`).value = get("consignor_panin", "consignee_panin") || "";
            document.getElementById(`${inputType}_district`).value = get("consignor_district", "consignee_district") || "";
            document.getElementById(`${inputType}_state`).value = get("consignor_state", "consignee_state") || "";

            document.getElementById(inputType + "-suggestions").innerHTML = "";
        }


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
        function calculateTotal() {
            // Retrieve all input values
            let basicFreight = parseFloat(document.querySelector("[name='basic_freight']").value) || 0;
            let documentCharge = parseFloat(document.querySelector("[name='document_charge']").value) || 0;
            let otherCharge = parseFloat(document.querySelector("[name='other_charge']").value) || 0;
            let doorCollection = parseFloat(document.querySelector("[name='door_collection']").value) || 0;
            let doorDelivery = parseFloat(document.querySelector("[name='door_delivery']").value) || 0;
            let fuel_surcharge = parseFloat(document.querySelector("[name='fuel_surcharge']").value) || 0;
            let handling_charge = parseFloat(document.querySelector("[name='handling_charge']").value) || 0;

            // Calculate total freight
            let totalFreight = basicFreight + documentCharge + otherCharge + doorCollection + doorDelivery + fuel_surcharge + handling_charge;
            document.querySelector("[name='total_freight']").value = totalFreight.toFixed(2);  // Set total freight

            // Calculate GST if checked
            let gstChecked = document.querySelector("[name='apply_gst']").checked;
            let gstAmount = 0;
            let cgstSgstAmount = 0;

            if (gstChecked) {
                gstAmount = totalFreight * 0.12; // 12% GST
                cgstSgstAmount = gstAmount / 2; // 6% CGST + 6% SGST
            }

            // Set GST amounts
            document.querySelector("[name='gst_amount']").value = gstAmount.toFixed(2);
            document.querySelector("[name='cgst_sgst_amount']").value = cgstSgstAmount.toFixed(2);

            // Calculate grand total
            let grandTotal = totalFreight + gstAmount;
            document.querySelector("[name='grand_total']").value = grandTotal.toFixed(2);
        }

        // Attach event listeners to input fields to trigger calculation on keyup or change
        document.addEventListener("keyup", calculateTotal);
        document.addEventListener("change", calculateTotal);
        let data1List = [];
        let data2List = [];
        let data3List = [];
        let numericValues = [];
        let totalSum = 0;



        function capitalizeWords(text) {
            return text.toLowerCase().split(" ").map(word =>
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(" ");
        }
        function showDistrictDropdown() {
            document.getElementById("district-dropdown").style.display = "block";
        }

        function hideDistrictDropdown() {
            setTimeout(() => {
                document.getElementById("district-dropdown").style.display = "none";
            }, 200); // small delay for click to register
        }

        function selectDistrict(value) {
            document.getElementById("consignor_district").value = value;
            hideDistrictDropdown();
        }

        function filterDistricts() {
            const input = document.getElementById("consignor_district").value.toLowerCase();
            const items = document.querySelectorAll("#district-dropdown .dropdown-item");

            items.forEach(item => {
                if (item.textContent.toLowerCase().includes(input)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });

            showDistrictDropdown();
        }

        // Hide dropdown if click outside
        document.addEventListener("click", function (e) {
            if (!document.getElementById("district-dropdown").contains(e.target) &&
                e.target.id !== "consignor_district") {
                hideDistrictDropdown();
            }
        });
        function showConsigneeDropdown() {
            document.getElementById("consignee-dropdown").style.display = "block";
        }

        function hideConsigneeDropdown() {
            setTimeout(() => {
                document.getElementById("consignee-dropdown").style.display = "none";
            }, 200);
        }

        function selectConsigneeDistrict(value) {
            document.getElementById("consignee_district").value = value;
            hideConsigneeDropdown();
        }

        function filterConsigneeDistricts() {
            const input = document.getElementById("consignee_district").value.toLowerCase();
            const items = document.querySelectorAll("#consignee-dropdown .dropdown-item");

            items.forEach(item => {
                if (item.textContent.toLowerCase().includes(input)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });

            showConsigneeDropdown();
        }

        document.addEventListener("click", function (e) {
            if (!document.getElementById("consignee-dropdown").contains(e.target) &&
                e.target.id !== "consignee_district") {
                hideConsigneeDropdown();
            }
        });
        function showConsigneeStateDropdown() {
            document.getElementById("consignee-state-dropdown").style.display = "block";
        }

        function filterConsigneeStates() {
            const input = document.getElementById("consignee_state").value.toLowerCase();
            const dropdownItems = document.querySelectorAll("#consignee-state-dropdown .dropdown-item");

            dropdownItems.forEach(item => {
                const value = item.textContent.toLowerCase();
                item.style.display = value.includes(input) ? "block" : "none";
            });
        }

        function selectConsigneeState(state) {
            document.getElementById("consignee_state").value = state;
            hideConsigneeStateDropdown();
        }

        function hideConsigneeStateDropdown() {
            document.getElementById("consignee-state-dropdown").style.display = "none";
        }

        // Close dropdown when clicking outside
        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("consignee-state-dropdown");
            const input = document.getElementById("consignee_state");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideConsigneeStateDropdown();
            }
        });
        function showConsignorStateDropdown() {
            document.getElementById("consignor-state-dropdown").style.display = "block";
        }

        function filterConsignorStates() {
            const input = document.getElementById("consignor_state").value.toLowerCase();
            const dropdownItems = document.querySelectorAll("#consignor-state-dropdown .dropdown-item");

            dropdownItems.forEach(item => {
                const value = item.textContent.toLowerCase();
                item.style.display = value.includes(input) ? "block" : "none";
            });
        }

        function selectConsignorState(state) {
            document.getElementById("consignor_state").value = state;
            hideConsignorStateDropdown();
        }

        function hideConsignorStateDropdown() {
            document.getElementById("consignor-state-dropdown").style.display = "none";
        }

        // Close dropdown when clicking outside
        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("consignor-state-dropdown");
            const input = document.getElementById("consignor_state");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideConsignorStateDropdown();
            }
        });
        function showSaidToContainDropdown() {
            document.getElementById("said-to-contain-dropdown").style.display = "block";
        }

        function filterSaidToContain() {
            const input = document.getElementById("said_to_contain").value.toLowerCase();
            const items = document.querySelectorAll("#said-to-contain-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectSaidToContain(value) {
            document.getElementById("said_to_contain").value = value;
            hideSaidToContainDropdown();
        }

        function hideSaidToContainDropdown() {
            document.getElementById("said-to-contain-dropdown").style.display = "none";
        }

        // Hide dropdown on click outside
        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("said-to-contain-dropdown");
            const input = document.getElementById("said_to_contain");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideSaidToContainDropdown();
            }
        });
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</body>

</html>