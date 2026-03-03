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
require './dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
if (isset($_POST['billid'])) {
    $billid = $_POST['billid']; // or fetch from a session variable if needed
    // Fetch the existing data from the database
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE billid = ?");
    $stmt->bind_param("s", $billid);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

} else {
    echo "Bill ID not provided!";
    echo "<script>window.history.go(-1);</script>";
    exit;
}

// Update the record if form is submitted

if (isset($_POST['submit'])) {

    // Assign POST variables to clean variables
    $billid = $_POST['billid']; // Assuming billid is passed from the form
    $consignor_name = $_POST['consignor_name'] ?? '';
    $consignor_phone = $_POST['consignor_phone'] ?? '';
    $consignor_email = $_POST['consignor_email'] ?? '';
    $consignor_gstin = $_POST['consignor_gstin'] ?? '';
    $consignor_address = $_POST['consignor_address'] ?? '';
    $consignor_district = $_POST['consignor_district'] ?? '';
    $consignor_state = $_POST['consignor_state'] ?? '';
    $consignor_pincode = $_POST['consignor_pincode'] ?? '';

    $consignee_name = $_POST['consignee_name'] ?? '';
    $consignee_phone = $_POST['consignee_phone'] ?? '';
    $consignee_email = $_POST['consignee_email'] ?? '';
    $consignee_gstin = $_POST['consignee_gstin'] ?? '';
    $consignee_address = $_POST['consignee_address'] ?? '';
    $consignee_district = $_POST['consignee_district'] ?? '';
    $consignee_state = $_POST['consignee_state'] ?? '';
    $consignee_pincode = $_POST['consignee_pincode'] ?? '';
    $consignee_panin = $_POST['consignee_panin'] ?? '';
    $consignor_panin = $_POST['consignor_panin'] ?? '';
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
    $billdate = $_POST['date'];
    $billtime = $data['billtime'];
    $apply_gst = (isset($_POST['apply_gst']) && $_POST['apply_gst'] === 'Yes') ? 'Yes' : 'No';
    $fast = (isset($_POST['fast']) && $_POST['fast'] === 'Yes') ? 'Yes' : 'No';
    $tod = date('d-M-Y h:iA');
    $tod2 = date('Y-m-d');
    $status = $_POST['transit_status'] ?? 'Not_initiated';

    $stmt = $conn->prepare("UPDATE invoices SET 
    ref1 = ?, consignor_name = ?, consignor_phone = ?, consignor_email = ?, consignor_gstin = ?, consignor_panin = ?, 
    consignor_address = ?, consignor_district = ?, consignor_state = ?, consignor_pincode = ?, consignee_name = ?, 
    consignee_phone = ?, consignee_email = ?, consignee_gstin = ?, consignee_panin = ?, consignee_address = ?, 
    consignee_district = ?, consignee_state = ?, consignee_pincode = ?, no_of_articles = ?, invoice_no = ?, 
    invoice_date = ?, ewaybill_no = ?, said_to_contain = ?, actual_weight = ?, charged_weight = ?, goods_value = ?, 
    value_sep = ?, basic_freight = ?, document_charge = ?, other_charge = ?, fuel_surcharge = ?, handling_charge = ?, 
    door_collection = ?, door_delivery = ?, total_freight = ?, gst_amount = ?, grand_total = ?, apply_gst = ?, 
     paymentMode = ?, created_by = ?, billdate = ?, transit_status = ? 
WHERE billid = ?");

    // Correct bind_param call
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssdddddddddsssssss",
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
        $paymentMode,
        $location,
        $billdate,
        $status,
        $billid

    );

    // Execute statement
    if ($stmt->execute()) {
        function convertImageToBase64($filePath)
        {
            if (!file_exists($filePath)) {
                return ''; // Return empty if file does not exist
            }

            $imageData = file_get_contents($filePath);
            return 'data:image/png;base64,' . base64_encode($imageData);
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
        if ($length > 100) {
            $fontSize = "5px";
        }
        if ($length2 > 50) {
            $fontSize2 = "7px";
        }
        if ($length2 > 80) {
            $fontSize2 = "6px";
        }
        if ($length2 > 100) {
            $fontSize2 = "5px";
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
            <td colspan="3" class="border-top-right">' . $billid . ' </td>
        </tr>
        <tr>
             <td colspan="3" class="border-left">Gstin:33EUOPK3413F1ZS</td>
            <td class="border-left">Date</td>
            <td>' . $billdate . '</td>
            <td>Time</td>
            <td class="border-right">' . $billtime . '</td>
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
            <td rowspan="2" colspan="3" class="border-right" style="font-size:' . $fontSize . ';">' . $consignor_address . '</td>


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
            <td colspan="3" class="border-top-right">' . $billid . ' </td>
        </tr>
        <tr>
             <td colspan="3" class="border-left">Gstin:33EUOPK3413F1ZS</td>
            <td class="border-left">Date</td>
            <td>' . $billdate . '</td>
            <td>Time</td>
            <td class="border-right">' . $billtime . '</td>
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
        $pdfPath = $folderPath . $billid . ".pdf";

        // Save the PDF file (Only Save, No Download)
        if (file_put_contents($pdfPath, $dompdf->output()) === false) {
            die("Failed to save PDF to " . $pdfPath);
        }
        echo "<div class='alert alert-success mt-3'>Invoice successfully updated.</div>";
        echo "<script>setTimeout(function() { window.history.go(-2); }, 2000);</script>";

    } else {
        echo "Error: " . $stmt->error;
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
                <a href="#"><i class="bx bxs-inbox icon" class="active"></i> Operation <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown show">
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
                                <select id="consignmentType" class="form-select mt-2" name="paymentMode">
                                    <option value="Topay" <?= $data['paymentMode'] == 'Topay' ? 'selected' : '' ?>>Topay
                                    </option>
                                    <option value="TBB" <?= $data['paymentMode'] == 'TBB' ? 'selected' : '' ?>>TBB</option>
                                    <option value="Paid" <?= $data['paymentMode'] == 'Paid' ? 'selected' : '' ?>>Paid
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="poNumber">PO Number/Ref 1</label>
                                <input type="text" id="poNumber" class="form-control mt-2" name="ref1"
                                    value="<?= $data['ref1'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <label for="date">Bill Date</label>
                                <input type="date" name="date" class="form-control mt-2"
                                    value="<?php echo $data['billdate'] ?>">
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
                                            placeholder="Enter Invoice Number" value=""></td>
                                    <td><input type="date" id="field4" class="form-control"
                                            placeholder="Enter Invoice Date" value=""></td>
                                    <td><input type="text" id="field2" class="form-control"
                                            placeholder="Enter Eway Bill Number" value=""><small id="ewaybill-error"
                                            style="color: red;font-size:10px;display:none"></small>
                                    </td>
                                    <td><input type="number" id="field3" class="form-control"
                                            placeholder="Enter Invoice Value" value=""></td>
                                    <td><button class="btn btn-primary btn-sm" type="button"
                                            onclick="addData()">Add</button></td>
                                </tr>
                                <!-- Add hidden fields to store invoice data -->
                                <tr>
                                    <td colspan="5">
                                        <div id="boxContainer" class="bubble-container"></div>
                                        <input type="hidden" id="hiddenData1" name="invoice_no"
                                            value="<?= $data['invoice_no'] ?>">
                                        <input type="hidden" id="hiddenData2" name="ewaybill_no"
                                            value="<?= $data['ewaybill_no'] ?>">
                                        <input type="hidden" id="hiddenData3" name="invoice_date"
                                            value="<?= $data['invoice_date'] ?>">
                                        <input type="hidden" id="hiddenTotal" name="goods_value"
                                            value="<?= $data['goods_value'] ?>">
                                        <input type="hidden" id="hiddenNumericValues" name="value_sep"
                                            value="<?= $data['value_sep'] ?>">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Consignor & Consignee Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Consignor</label>
                            <input type="text" class="form-control my-2" placeholder="Company Name"
                                name="consignor_name" value="<?= $data['consignor_name'] ?>" autocomplete="off" />
                            <input type="text" class="form-control my-2" placeholder="Address" name="consignor_address"
                                value="<?= $data['consignor_address'] ?>" autocomplete="off" />

                            <!-- Consignor Email and Phone -->
                            <input type="email" class="form-control my-2" placeholder="Email" name="consignor_email"
                                value="<?= $data['consignor_email'] ?>" autocomplete="off" />
                            <input type="tel" class="form-control my-2" placeholder="Phone" name="consignor_phone"
                                value="<?= $data['consignor_phone'] ?>" autocomplete="off" />

                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control my-2" placeholder="Pin"
                                        name="consignor_pincode" value="<?= $data['consignor_pincode'] ?>"
                                        autocomplete="off" />
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignor_district"
                                            name="consignor_district" placeholder="Enter or select district"
                                            autocomplete="off"
                                            value="<?= htmlspecialchars($data['consignor_district'] ?? '') ?>"
                                            onfocus="showConsignorDistrictDropdown()"
                                            oninput="filterConsignorDistricts()" required>

                                        <div id="consignor-district-dropdown" class="dropdown-menu w-100 shadow"
                                            style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                            <?php
                                            $stmt = $conn->prepare("SELECT name FROM districts ORDER BY name ASC");
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $district = htmlspecialchars($row['name']);
                                                echo "<div class='dropdown-item' onclick=\"selectConsignorDistrict('$district')\">$district</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 my-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignor_state"
                                            name="consignor_state" placeholder="Enter or select state"
                                            autocomplete="off"
                                            value="<?= htmlspecialchars($data['consignor_state'] ?? '') ?>"
                                            onfocus="showConsignorStateDropdown()" oninput="filterConsignorStates()"
                                            required>

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
                                    <input type="text" class="form-control my-2" placeholder="GST Number"
                                        name="consignor_gstin" value="<?= $data['consignor_gstin'] ?>"
                                        autocomplete="off" />
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="Pan Number"
                                        name="consignor_panin" value="<?= $data['consignor_panin'] ?>"
                                        autocomplete="off" />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label>Consignee</label>
                            <input type="text" class="form-control my-2" placeholder="Company Name"
                                name="consignee_name" value="<?= $data['consignee_name'] ?>" autocomplete="off" />
                            <input type="text" class="form-control my-2" placeholder="Address" name="consignee_address"
                                value="<?= $data['consignee_address'] ?>" autocomplete="off" />

                            <!-- Consignee Email and Phone -->
                            <input type="email" class="form-control my-2" placeholder="Email" name="consignee_email"
                                value="<?= $data['consignee_email'] ?>" autocomplete="off" />
                            <input type="tel" class="form-control my-2" placeholder="Phone" name="consignee_phone"
                                value="<?= $data['consignee_phone'] ?>" autocomplete="off" />

                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control my-2" placeholder="Pin"
                                        name="consignee_pincode" value="<?= $data['consignee_pincode'] ?>"
                                        autocomplete="off" />
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input type="text" class="form-control my-2" id="consignee_district"
                                            name="consignee_district" placeholder="Enter or select district"
                                            autocomplete="off"
                                            value="<?= htmlspecialchars($data['consignee_district'] ?? '') ?>"
                                            onfocus="showConsigneeDistrictDropdown()"
                                            oninput="filterConsigneeDistricts()" required>

                                        <div id="consignee-district-dropdown" class="dropdown-menu w-100 shadow"
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
                                            autocomplete="off"
                                            value="<?= htmlspecialchars($data['consignee_state'] ?? '') ?>"
                                            onfocus="showConsigneeStateDropdown()" oninput="filterConsigneeStates()"
                                            required>

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
                                        name="consignee_gstin" value="<?= $data['consignee_gstin'] ?>"
                                        autocomplete="off" />
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control my-2" placeholder="Pan Number"
                                        name="consignee_panin" value="<?= $data['consignee_panin'] ?>"
                                        autocomplete="off" />
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Invoice Details -->
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="No of Articles"
                                    name="no_of_articles" value="<?= $data['no_of_articles'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Charged Weight"
                                    name="charged_weight" value="<?= $data['charged_weight'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Actual Weight"
                                    name="actual_weight" value="<?= $data['actual_weight'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative">
                                    <input type="text" class="form-control my-2" id="said_to_contain"
                                        name="said_to_contain" placeholder="Enter or select item" autocomplete="off"
                                        value="<?= htmlspecialchars($data['said_to_contain'] ?? '') ?>" onfocus="
                                        showContainDropdown()" oninput="filterContainItems()" required>

                                    <div id="contain-dropdown" class="dropdown-menu w-100 shadow"
                                        style="max-height: 200px; overflow-y: auto; display: none; position: absolute; z-index: 1000;">
                                        <?php
                                        $stmt = $conn->prepare("SELECT name FROM items ORDER BY name ASC");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            $item = htmlspecialchars($row['name']);
                                            echo "<div class='dropdown-item' onclick=\"selectContainItem('$item')\">$item</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Freight & Charges -->
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Basic Freight"
                                    name="basic_freight" value="<?= $data['basic_freight'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Document Charge"
                                    name="document_charge" value="<?= $data['document_charge'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Other Charges"
                                    name="other_charge" value="<?= $data['other_charge'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Door Collection"
                                    name="door_collection" value="<?= $data['door_collection'] ?>" />
                            </div>
                            <div class="col-md-3">

                                <input type="number" class="form-control" name="fuel_surcharge"
                                    value="<?= $data['fuel_surcharge'] ?>" />
                            </div>
                            <div class="col-md-3">

                                <input type="number" class="form-control" name="handling_charge"
                                    value="<?= $data['handling_charge'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Door Delivery"
                                    name="door_delivery" value="<?= $data['door_delivery'] ?>" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Total Freight"
                                    name="total_freight" value="<?= $data['total_freight'] ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="row">


                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="GST Amount"
                                    name="gst_amount" value="<?= $data['gst_amount'] ?>" step="any" />
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control my-2" placeholder="Grand Total"
                                    name="grand_total" value="<?= $data['grand_total'] ?>" />
                                <input type="hidden" name="billid" value="<?= $billid; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Apply GST -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="apply_gst" Value="Yes"
                            <?= $data['apply_gst'] == 'Yes' ? 'checked' : '' ?> />
                        <label class="form-check-label" for="applyGST">Apply GST</label>
                        <input class="form--input ms-3" type="checkbox" name="fast" Value="Yes" <?= $data['fast'] == 'Yes' ? 'checked' : '' ?> />
                        <label class="form-check-label" for="applyGST">FTL</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary mt-3" name="submit">Update Consignment</button>
            </form>

        </div>
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
                gstAmount = totalFreight * 0.12; // 16% GST
                cgstSgstAmount = gstAmount / 2; // 6% CGST + 6% SGST
            }

            // Set GST amounts
            document.querySelector("[name='gst_amount']").value = gstAmount.toFixed(2);
            document.querySelector("[name='cgst_sgst_amount']").value = cgstSgstAmount.toFixed(2);

            // Calculate grand total
            let grandTotal = totalFreight + gstAmount;
            document.querySelector("[name='grand_total']").value = grandTotal.toFixed(2);
        }
    </script>
    <script>
        // Attach event listeners to input fields
        document.addEventListener("keyup", calculateTotal);
        document.addEventListener("change", calculateTotal);

        let data1List = [];
        let data2List = [];
        let data3List = [];
        let numericValues = [];
        let totalSum = 0;
        document.addEventListener("DOMContentLoaded", function () {
            const data1 = document.getElementById("hiddenData1").value.split(','); // Invoice numbers
            const data2 = document.getElementById("hiddenData2").value.split(','); // E-way bill numbers (may be empty)
            const data3 = document.getElementById("hiddenData3").value.split(','); // Invoice dates
            const numericData = document.getElementById("hiddenNumericValues").value.split(','); // Invoice values
            const totalSumValue = parseFloat(document.getElementById("hiddenTotal").value) || 0;

            const dataLength = Math.min(data1.length, data2.length, data3.length, numericData.length);

            for (let i = 0; i < dataLength; i++) {
                const field1 = data1[i].trim(); // Invoice number
                const field2 = data2[i]?.trim() || ""; // E-way Bill (may be empty)
                const field4 = data3[i].trim(); // Invoice date
                const field3 = parseFloat(numericData[i].trim()); // Invoice value

                if (field1 && !isNaN(field3) && field4) {
                    const id = `inv_${Date.now()}_${i}`; // Unique ID

                    // Push the data into the respective arrays
                    data1List.push({ id, value: field1 });
                    data2List.push({ id, value: field2 });
                    data3List.push({ id, value: field4 });
                    numericValues.push({ id, value: field3 });

                    // Create the bubble
                    const boxContainer = document.getElementById("boxContainer");
                    const box = document.createElement("div");
                    box.className = "floating-box";
                    box.setAttribute("data-id", id);
                    box.innerHTML = `
                <span>${field1} - ${field4} - ${field2} - ${field3.toFixed(2)}</span>
                <span class="edit-btn" onclick="editData('${id}')">✎</span>
                <span class="remove-btn" onclick="removeData('${id}', ${field3})">×</span>
            `;
                    boxContainer.appendChild(box);
                }
            }

            totalSum = totalSumValue;
            console.log("Loaded Total:", totalSum);
        });
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        document.querySelectorAll('.suggestion-field').forEach((input) => {
            input.addEventListener('keyup', function () {
                let query = this.value;
                let field = this.name;

                if (query.length > 1) {
                    fetch('autosuggest.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ query: query, field: field })
                    })
                        .then(response => response.text())
                        .then(data => {
                            let suggestionBox = document.querySelector('#suggestions-' + field);
                            suggestionBox.innerHTML = data;
                            suggestionBox.style.display = 'block';

                            // Populate input on click
                            document.querySelectorAll('.suggestion-item').forEach(item => {
                                item.addEventListener('click', function () {
                                    let details = JSON.parse(this.getAttribute('data-details'));
                                    input.value = details.name;
                                    document.querySelector('input[name="consignor_address"]').value = details.address;
                                    document.querySelector('input[name="consignor_pincode"]').value = details.pincode;
                                    suggestionBox.style.display = 'none';
                                });
                            });
                        });
                } else {
                    document.querySelector('#suggestions-' + field).style.display = 'none';
                }
            });
        });

        function showConsignorDistrictDropdown() {
            document.getElementById("consignor-district-dropdown").style.display = "block";
        }

        function filterConsignorDistricts() {
            const input = document.getElementById("consignor_district").value.toLowerCase();
            const items = document.querySelectorAll("#consignor-district-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectConsignorDistrict(value) {
            document.getElementById("consignor_district").value = value;
            hideConsignorDistrictDropdown();
        }

        function hideConsignorDistrictDropdown() {
            document.getElementById("consignor-district-dropdown").style.display = "none";
        }

        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("consignor-district-dropdown");
            const input = document.getElementById("consignor_district");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideConsignorDistrictDropdown();
            }
        });
        function showConsigneeDistrictDropdown() {
            document.getElementById("consignee-district-dropdown").style.display = "block";
        }

        function filterConsigneeDistricts() {
            const input = document.getElementById("consignee_district").value.toLowerCase();
            const items = document.querySelectorAll("#consignee-district-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectConsigneeDistrict(value) {
            document.getElementById("consignee_district").value = value;
            hideConsigneeDistrictDropdown();
        }

        function hideConsigneeDistrictDropdown() {
            document.getElementById("consignee-district-dropdown").style.display = "none";
        }

        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("consignee-district-dropdown");
            const input = document.getElementById("consignee_district");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideConsigneeDistrictDropdown();
            }
        });
        function showConsigneeStateDropdown() {
            document.getElementById("consignee-state-dropdown").style.display = "block";
        }

        function filterConsigneeStates() {
            const input = document.getElementById("consignee_state").value.toLowerCase();
            const items = document.querySelectorAll("#consignee-state-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectConsigneeState(value) {
            document.getElementById("consignee_state").value = value;
            hideConsigneeStateDropdown();
        }

        function hideConsigneeStateDropdown() {
            document.getElementById("consignee-state-dropdown").style.display = "none";
        }

        // Hide dropdown if clicked outside
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
            const items = document.querySelectorAll("#consignor-state-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectConsignorState(value) {
            document.getElementById("consignor_state").value = value;
            hideConsignorStateDropdown();
        }

        function hideConsignorStateDropdown() {
            document.getElementById("consignor-state-dropdown").style.display = "none";
        }

        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("consignor-state-dropdown");
            const input = document.getElementById("consignor_state");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideConsignorStateDropdown();
            }
        });
        function showContainDropdown() {
            document.getElementById("contain-dropdown").style.display = "block";
        }

        function filterContainItems() {
            const input = document.getElementById("said_to_contain").value.toLowerCase();
            const items = document.querySelectorAll("#contain-dropdown .dropdown-item");

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(input) ? "block" : "none";
            });
        }

        function selectContainItem(value) {
            document.getElementById("said_to_contain").value = value;
            hideContainDropdown();
        }

        function hideContainDropdown() {
            document.getElementById("contain-dropdown").style.display = "none";
        }

        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("contain-dropdown");
            const input = document.getElementById("said_to_contain");
            if (!dropdown.contains(e.target) && e.target !== input) {
                hideContainDropdown();
            }
        });
    </script>
</body>

</html>
<?php


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

?>