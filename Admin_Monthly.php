<?php
require './dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
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
$Nameadmin = '';
$stmt = $conn->prepare("SELECT Incharge FROM admin WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$stmt->bind_result($Nameadmin);
$stmt->fetch();
$stmt->close();
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
function numberToWords($num)
{
    $ones = array(
        0 => "zero",
        1 => "one",
        2 => "two",
        3 => "three",
        4 => "four",
        5 => "five",
        6 => "six",
        7 => "seven",
        8 => "eight",
        9 => "nine",
        10 => "ten",
        11 => "eleven",
        12 => "twelve",
        13 => "thirteen",
        14 => "fourteen",
        15 => "fifteen",
        16 => "sixteen",
        17 => "seventeen",
        18 => "eighteen",
        19 => "nineteen"
    );

    $tens = array(
        0 => "",
        1 => "ten",
        2 => "twenty",
        3 => "thirty",
        4 => "forty",
        5 => "fifty",
        6 => "sixty",
        7 => "seventy",
        8 => "eighty",
        9 => "ninety"
    );

    if ($num == 0) {
        return "zero";
    }

    $words = "";

    // Split into crore, lakh, thousand, hundred, remainder
    $crores = floor($num / 10000000);
    $num %= 10000000;

    $lakhs = floor($num / 100000);
    $num %= 100000;

    $thousands = floor($num / 1000);
    $num %= 1000;

    $hundreds = floor($num / 100);
    $remainder = $num % 100;

    if ($crores > 0) {
        $words .= numberToWords($crores) . " crore ";
    }
    if ($lakhs > 0) {
        $words .= numberToWords($lakhs) . " lakh ";
    }
    if ($thousands > 0) {
        $words .= numberToWords($thousands) . " thousand ";
    }
    if ($hundreds > 0) {
        $words .= numberToWords($hundreds) . " hundred ";
    }
    if ($remainder > 0) {
        if ($remainder < 20) {
            $words .= $ones[$remainder] . " ";
        } else {
            $words .= $tens[floor($remainder / 10)];
            if ($remainder % 10 > 0) {
                $words .= "-" . $ones[$remainder % 10];
            }
            $words .= " ";
        }
    }

    return trim($words);
}

$states = [
    "TAMIL NADU" => "33",
    "KERALA" => "32",
    "PUDUCHERRY" => "34",
    "MAHARASTRA" => "27",
    "KARNATAKA" => "29",
    "DELHI" => "07",
    "JAMMU AND KASHMIR" => "01",
    "HIMACHAL PRADESH" => "02",
    "PUNJAB" => "03",
    "CHANDIGARH" => "04",
    "UTTARAKHAND" => "05",
    "HARYANA" => "06",
    "RAJASTHAN" => "08",
    "UTTAR PRADESH" => "09",
    "BIHAR" => "10",
    "SIKKIM" => "11",
    "ARUNACHAL PRADESH" => "12",
    "NAGALAND" => "13",
    "MANIPUR" => "14",
    "MIZORAM" => "15",
    "TRIPURA" => "16",
    "MEGHALAYA" => "17",
    "ASSAM" => "18",
    "WEST BENGAL" => "19",
    "JHARKHAND" => "20",
    "ORISSA" => "21",
    "CHHATTISGARH" => "22",
    "MADHYA PRADESH" => "23",
    "GUJARAT" => "24",
    "DAMAN AND DIU" => "25",
    "DADAR AND NAGAR HAVELI" => "26",
    "GOA" => "30",
    "LAKSHADWEEP" => "31",
    "ANDAMAN AND NICOBAR" => "35",
    "TELANGANA" => "36",
    "ANDHRA PRADESH" => "37",
    "OTHER TERRITORY" => "97",
    "OTHER COUNTRY" => "96"
];


// Function: Generate 15 digit ackno
function generateAckno($conn)
{
    do {
        $prefix = "2025"; // fixed prefix (you can choose anything)
        $random = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
        $ackno = str_pad($prefix, 10, "0", STR_PAD_RIGHT) . $random;
        $check = $conn->query("SELECT 1 FROM monthlyreport  WHERE ackno='$ackno'");
    } while ($check->num_rows > 0);
    return $ackno;
}

// Default auto PAN
$autoPAN = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerName = $_POST['customerName'];
    $customerAddr = $_POST['customerAddress'];
    $panNo = $_POST['panNo'];
    $gstNo = $_POST['gstNo'];
    $state = $_POST['state'];
    $stateCode = $_POST['stateCode'];
    $invoiceNo = $_POST['invoiceNo'];
    $invoiceDate = $_POST['invoiceDate'];
    $particular = $_POST['particular'];
    $sacCode = $_POST['sacCode'];
    $amount = (float) $_POST['amount'];
    $applyGST = isset($_POST['applygst']) ? 1 : 0;

    // Tax calc
    if ($applyGST) {
        $cgst = $amount * 0.06;
        $sgst = $amount * 0.06;
    } else {
        $cgst = 0;
        $sgst = 0;
    }

    $total = $amount + $cgst + $sgst;

    date_default_timezone_set("Asia/Kolkata"); // set IST

    $currentDate = date("D d-M-Y H:i:s T");
    // Tax calc

    $totalText = strtoupper(numberToWords(round($total)));

    // Ackno
    $ackno = generateAckno($conn);

    // Insert DB
    $stmt = $conn->prepare("INSERT INTO monthlyreport  
        (customerName, customerAddress, panNo, gstNo, state, stateCode, invoiceNo, invoiceDate, particular, sacCode, amount, cgst, sgst, total, totalText,ackno,preparedby,	preparedat ,applygst)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssssssssddddsssss",
        $customerName,
        $customerAddr,
        $panNo,
        $gstNo,
        $state,
        $stateCode,
        $invoiceNo,
        $invoiceDate,
        $particular,
        $sacCode,
        $amount,
        $cgst,
        $sgst,
        $total,
        $totalText,
        $ackno,
        $Nameadmin,
        $currentDate,
        $applyGST
    );
    if ($stmt->execute()) {

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        function convertImageToBase64($filePath)
        {
            if (!file_exists($filePath)) {
                return ''; // Return empty if file does not exist
            }

            $imageData = file_get_contents($filePath);
            return 'data:image/png;base64,' . base64_encode($imageData);
        }
        $logoPath = __DIR__ . "/Asset/Logo.jpg";
        $base64Image = convertImageToBase64($logoPath);
        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Tax Invoice</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 10px;font-size:10px; }
        table { width: 100%; border-collapse: collapse;margin-top:50px;}
        .bordered { border: 1px solid black; }
        .no-border { border: none; }
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .border-bottom { border-bottom: 1px solid black; }
        .border-leftright { border-left:1px solid black !important;border-right:1px solid black !important; }
        .border-left { border-left:1px solid black !important; }
        .signature-line { display: inline-block; width: 150px; border-top: 1px solid black; }
        .row-line{line-height:12px;}
       td, th {
    padding: 0px 12px; /* top/bottom 8px, left/right 12px */
}
    .heading{
    font-size:8px;
    font-weight:bolder;
    padding-top:2px;
    }

    </style>
</head>
<body>
    <table class='bordered' style='padding:12px !important;'>
        <!-- Header Row with Logo and QR -->
        <tr>
            <td class='no-border border-bottom' rowspan='3' colspan='2'vertical-align: top;'>
          <img src='$base64Image' style='height:107px;
            width: 148px;'>
            </td>
            <td class='no-border' colspan='5' >
                <div style='font-size: 18px; font-weight: bold;padding-bottom:10px;'>SV Logistics Private Limited</div>
                <div class='heading'>Admin Off : 3/206,Kulathur Road,Venkittapuram,Coimbatore - 641062</div>
            </td>
     
        </tr>  
        <tr>
            <td class='no-border heading' colspan='5' style='padding-top:12px;'>
                Phone No.: +91 95851 56817 / E-Mail : svlogistics.sales@gmail.com
            </td>
        </tr>
        <tr>
            <td class='no-border border-bottom heading' colspan='5' style='font-size:6.5px;'>
          
                GST No.:33EUOPK3413F1ZS/PAN No. EUOPK3413F1ZS/ Udyam Registration No. : UDYAM-TN-03-0237286
            </td>
        </tr>
        <tr>
            <td class='no-border border-bottom' colspan='3'>
                <strong>Ack No:</strong> " . $ackno . "
            </td>
            <td class='no-border border-bottom' colspan='4'>
                <strong>Ack Date:</strong>" . $invoiceDate . "
            </td>
        </tr>

        <!-- Invoice Title -->
        <tr>
            <td class='no-borderq border-bottom' colspan='7' style='font-size: 16px; font-weight: bold; padding-top: 5px;text-align:center;'>TAX INVOICE</td>
        </tr>

        <!-- Customer and Invoice Details Table -->
        <tr >
            <td class='border-left' colspan='1'><strong>CUSTOMER:</strong></td>
            <td colspan='3'  style='padding-bottom:5px;padding-left:0px'>: " . $customerName . "</td>
            <td class='border-left' colspan='3' style='padding-bottom:5px;'><strong>INVOICE NUMBER &nbsp;&nbsp;&nbsp;  </strong>:  " . $invoiceNo . "</td>
     
        </tr>
        <tr>
            <td class='border-left' colspan='1' rowspan='3'><strong>ADDRESS</strong></td>
            <td colspan='3' rowspan='3' style='padding-bottom:5px;padding-left:0px'>: " . $customerAddr . "</td>
          
            <td class='border-left' colspan='3' style='padding-bottom:5px;'><strong>DATE</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: " . $invoiceDate . "</td>
        </tr>
        <tr>
          
            <td class='border-left' colspan='3' style='padding-bottom:5px;'><strong>PLACE OF SERVICE &nbsp;&nbsp;</strong>: " . $state . "</td>
        </tr>
        <tr>
             <td class='border-left' colspan='3' style='padding-bottom:5px;'><strong>TERMS OF PAYMENT</strong>: 10 Days</td>
        </tr>
        <tr>
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>  <strong>PAN</strong><br></td>
            <td colspan='3' style='padding-bottom:5px;padding-left:0px'>: " . $panNo . "<br></td>
                     <td class='border-left' colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
            <td colspan='1' style='padding-bottom:5px;'>
            &nbsp;
            </td>
        </tr>
        <tr >
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>  <strong>GST No</strong><br></td>
            <td colspan='3' style='padding-bottom:5px;padding-left:0px'>: " . $gstNo . "</td>
            </td>
            <td class='border-left' colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
            <td colspan='1' style='padding-bottom:5px;'>
            &nbsp;
            </td>
        </tr>
        <!-- GST Details -->
        <tr>
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>

                <strong>State Code</strong>
            </td>
            <td colspan='3' style='padding-bottom:5px;padding-left:0px'> :" . $stateCode . "
            </td>
            <td class='border-left' colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
            <td colspan='1' style='padding-bottom:5px;'>
            &nbsp;
            </td>
        </tr>

        <!-- Items Table -->
        <tr class='bordered'>
            <td class='border-leftright bold' >SL NO.</td>
            <td class='no-border bold' colspan='3'>PARTICULARS</td>
            <td class='bordered bold border-leftright'>GST%</td>
            <td class='bordered bold'>SAC Code</td>
            <td class='bordered bold'>AMOUNT</td>
        </tr>
        ";
        if ($cgst == 0 && $sgst == 0) {
            $html .= "
        <tr>
            <td class='border-leftright border-bottom'  style='padding-bottom:100px;'>1</td>
            <td class='no-border border-bottom' colspan='3'  style='padding-bottom:100px;'>" . $particular . "</td>
            <td class='no-border border-leftright border-bottom'  style='padding-bottom:100px;'>12</td>
            <td class='no-border border-leftright border-bottom'  style='padding-bottom:100px;'>" . $sacCode . "</td>
            <td class='no-border text-right border-bottom'  style='padding-bottom:100px;'>" . $amount . "</td>
        </tr>
       
    ";
        } else {
            $html .= "
        <tr>
            <td class='border-leftright'>1</td>
            <td class='no-border' colspan='3'>" . $particular . "</td>
            <td class='no-border border-leftright'>12</td>
            <td class='no-border border-leftright'>" . $sacCode . "</td>
            <td class='no-border text-right'>" . $amount . "</td>
        </tr>
        <tr>
            <td class='border-leftright'>2</td>
            <td class='no-border' colspan='3'>TN SGST @ 6%</td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright text-right'>" . $sgst . "</td>
        </tr>
        <tr>
            <td class='border-leftright border-bottom' style='padding-bottom:100px;'>3</td>
            <td class='no-border border-bottom' colspan='3' style='padding-bottom:100px;'>TN CGST @ 6%</td>
            <td class='no-border border-leftright border-bottom' style='padding-bottom:100px;'></td>
            <td class='no-border border-leftright border-bottom' style='padding-bottom:100px;'></td>
            <td class='no-border border-leftright text-right border-bottom' style='padding-bottom:100px;'>" . $cgst . "</td>
        </tr>
    ";
        }
        $html .= "
        <!-- Total Section -->
        <tr>
            <td class='no-border text-right border-bottom' colspan='6' style='font-size: 16px; font-weight: bold;'>T O T A L</td>
              <td class='no-border text-right border-bottom'>" . $total . "</td>
        </tr>
        <tr>    
            <td class='no-border bold border-bottom'>Rupees</td>
            <td class='no-border border-bottom' colspan='6'>" . $totalText . "</td>
          
        </tr>

        <!-- Bank Details -->
        <tr>
            <td class='no-border'colspan='4'><strong>Bank Name :</strong> FFDERAL  Bank Limited </td>
            <td class='no-border' colspan='3'><strong>Virtual AC No :</strong> 24500200000678</td>
        </tr>
        <tr>
            <td class='no-border border-bottom' colspan='4'><strong>IFSC :</strong> FDRL0002450</td>
            <td class='no-border border-bottom' colspan='3'><strong>Branch :</strong> CHINNIYAMPALAYAM</td>
        </tr>
        <!-- Terms and Conditions -->
        <tr>
            <td class='no-border bold' colspan='7' style='padding-top: 5px;'>Terms & Conditions:</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>1.Interest @ 24% P.A will be levied on invoice amount if not paid within the Credit Period. </td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>2.Cheque/DD/RTGS/NEFT should be made to M/s. SV LOGISTICS </td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>3. Any discrepancy should be notified to us in writing within 7 days from the invoice date, otherwise it will be presumed that the amount reflected on the bill is correct and 
have been verified at your end. </td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>4. The owner must insure the goods against all risk.</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>5. Our responsibility ceases after the goods have been delivered to the carrier godown etc.</td>
        </tr>
  
        <tr>
            <td class='no-border' colspan='7' style='font-size:7px;'><em>Service rendered on the basis of orders / oral orders placed on the administrative office of SV Logistics</em></td>
        </tr>
        <tr>
            <td class='no-border border-bottom' colspan='7'></td>
        </tr>

        <!-- Signature Section -->
       <tr>
    <td colspan='7' style='text-align: right;'>
        <strong>For SV Logistics</strong>
    </td>
        </tr>

        <tr>
            <td colspan='7' style='text-align: center;' style='padding-bottom:10px;'>&nbsp;</td>
        </tr>
        <tr>
           <td colspan='7' style='text-align: right;' class='border-bottom'>
            Digitaly Signed by:<br> SV logictics<br>
            " . $currentDate . "<br>
            " . $Nameadmin . "
           </td>
        </tr>
        <tr>
            <td colspan='2' style='text-align: right;' class='border-bottom'>
                <strong>Prepared by</strong>
             
            </td>
            <td colspan='2' style='text-align: right;' class='border-bottom'>
                   <strong>Checked by</strong>
            </td>
            <td colspan='3' style='text-align: right;' class='border-bottom'>
      
                <strong>Authorized Signatory</strong>
            </td>
        </tr>
              <tr class='row-line'>
            <td class='bordered border-bottom' style='text-align:center' colspan='7'>Digitally Signed Invoice not required seal & sign </td>
        </tr>
    </table>
</body>
</html>
";
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Set Paper Size and Orientation (A4, Portrait)
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Define folder path
        $folderPath = "Monthly_bills/";

        // Ensure directory exists
        if (!is_dir($folderPath) && !mkdir($folderPath, 0777, true) && !is_dir($folderPath)) {
            die("Failed to create directory: " . $folderPath);
        }

        // Define PDF file path
        $pdfPath = $folderPath . $ackno . ".pdf";

        // Save the PDF file (Only Save, No Download)
        if (file_put_contents($pdfPath, $dompdf->output()) === false) {
            die("Failed to save PDF to " . $pdfPath);
        }
        echo "<div class='alert alert-success mt-3'>Data inserted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Error: " . $stmt->error . "</div>";
    }
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
                <ul class="side-dropdown">
                    <li><a href="Admin_Addbill.php">Waybills</a></li>
                    <li><a href="Admin_Todaybill.php">Today Waybills</a></li>
                    <li><a href="Admin_Totalbill.php">Total Waybills</a></li>
                </ul>
            </li>

            <li class="divider" data-text="Manifest">Manifest</li>
            <li>
                <a href="#" class="active"><i class="bx bxs-inbox icon"></i> Transits <i
                        class="bx bx-chevron-right icon-right"></i></a>
                <ul class="side-dropdown show">
                    <li><a href="Admin_Intransit.php">In Transits</a></li>
                    <li><a href="Admin_VehicleOUT.php">Vehicle OUT</a></li>
                    <li><a href="Admin_VehicleIN.php" style="color: #2f3192">Vehicle IN</a></li>
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
        <!-- MAIN -->
        <main>
            <h1 class="title">Dashboard</h1>
            <ul class="breadcrumbs p-0">
                <li><a href="#">Waybills</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Monthly</a></li>
            </ul>
        </main>
        <!-- MAIN -->
        <div class="container form-container">
            <div class="container">
                <div class="card shadow p-4">
                    <h3 class="mb-3">Invoice Form</h3>
                    <form method="post">
                        <div class="row mb-3">
                            <div class="col">
                                <label>Customer Name</label>
                                <input type="text" name="customerName" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Customer Address</label>
                                <input type="text" name="customerAddress" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label>GST No</label>
                                <input type="text" id="gstNo" name="gstNo" class="form-control" maxlength="15"
                                    oninput="validateGST()">
                            </div>
                            <div class="col">
                                <label>PAN No</label>
                                <input type="text" id="panNo" name="panNo" class="form-control" readonly>
                            </div>

                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label>State</label>
                                <select name="state" id="state" class="form-select" onchange="updateStateCode()"
                                    required>
                                    <option value="">Select State</option>
                                    <?php foreach ($states as $st => $code) { ?>
                                        <option value="<?= $st ?>" data-code="<?= $code ?>"><?= $st ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col">
                                <label>State Code (Auto)</label>
                                <input type="text" id="stateCode" name="stateCode" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label>Invoice No</label>
                                <input type="text" name="invoiceNo" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Date</label>
                                <input type="date" name="invoiceDate" value="<?= date('Y-m-d') ?>" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label>Particular</label>
                                <input type="text" name="particular" class="form-control">
                            </div>
                            <div class="col">
                                <label>SAC Code</label>
                                <input type="text" name="sacCode" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 mb-2">
                                <label>
                                    <input type="checkbox" id="applygst" name="applygst" value="1"
                                        onclick="calcTaxes()"> Apply GST
                                </label>
                            </div>
                            <div class="col">
                                <label>Amount</label>
                                <input type="number" step="0.01" id="amount" name="amount" class="form-control"
                                    oninput="calcTaxes()" required>
                            </div>
                            <div class="col">
                                <label>CGST (6%)</label>
                                <input type="text" id="cgst" name="cgst" class="form-control" readonly>
                            </div>
                            <div class="col">
                                <label>SGST (6%)</label>
                                <input type="text" id="sgst" name="sgst" class="form-control" readonly>
                            </div>
                            <div class="col">
                                <label>Total</label>
                                <input type="text" id="total" name="total" class="form-control" readonly>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Invoice</button>
                    </form>
                </div>
            </div>
        </div>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function updateStateCode() {
            let stateDropdown = document.getElementById("state");
            let stateCodeField = document.getElementById("stateCode");
            let selectedCode = stateDropdown.options[stateDropdown.selectedIndex].getAttribute("data-code");
            let pan = document.getElementById("panNo").value;
            let suffix = "1Z";
            if (selectedCode && pan) {
                stateCodeField.value = selectedCode + pan + suffix + "F"; // dummy check digit
            }
        }

        // ✅ Real-time GST validation
        function validateGST() {
            let gst = document.getElementById("gstNo").value.trim().toUpperCase();
            let panField = document.getElementById("panNo");
            let stateDropdown = document.getElementById("state");
            let stateCodeField = document.getElementById("stateCode");

            // Regex for GSTIN
            let gstRegex = /^([0-9]{2})([A-Z]{5}[0-9]{4}[A-Z])([0-9])(Z)([A-Z0-9])$/;

            if (gst.length < 15) {
                // while typing - just reset fields
                panField.value = "";
                stateCodeField.value = "";
                return;
            }

            if (!gstRegex.test(gst)) {
                stateCodeField.value = "❌ Invalid GST";
                panField.value = "";
                return;
            }

            // Extract state code + PAN
            let stateCode = gst.substring(0, 2);
            let pan = gst.substring(2, 12);

            // Fill PAN field
            panField.value = pan;

            // Try to auto-select state
            for (let i = 0; i < stateDropdown.options.length; i++) {
                if (stateDropdown.options[i].getAttribute("data-code") === stateCode) {
                    stateDropdown.selectedIndex = i;
                    break;
                }
            }

            // Show GST itself in stateCode box (full)
            stateCodeField.value = stateCode;
        }
        function calcTaxes() {
            let amt = parseFloat(document.getElementById("amount").value) || 0;
            let applyGST = document.getElementById("applygst").checked;

            let cgst = 0, sgst = 0, total = amt;

            if (applyGST) {
                cgst = amt * 0.06;
                sgst = amt * 0.06;
                total = amt + cgst + sgst;
            }

            document.getElementById("cgst").value = cgst.toFixed(2);
            document.getElementById("sgst").value = sgst.toFixed(2);
            document.getElementById("total").value = total.toFixed(2);
        }
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


    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>