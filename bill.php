<?php
require './dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
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
                <strong>Ack No:</strong> 152521590117285
            </td>
            <td class='no-border border-bottom' colspan='4'>
                <strong>Ack Date:</strong> 5-May-25
            </td>
        </tr>

        <!-- Invoice Title -->
        <tr>
            <td class='no-borderq border-bottom' colspan='7' style='font-size: 16px; font-weight: bold; padding-top: 5px;text-align:center;'>TAX INVOICE</td>
        </tr>

        <!-- Customer and Invoice Details Table -->
        <tr>
            <td class='border-left' colspan='1'><strong>CUSTOMER:</strong></td>
            <td colspan='2'  style='padding-bottom:5px;'>: BALAJI ELECTRONICS</td>
            <td class='border-left' colspan='2' style='padding-bottom:5px;'><strong>INVOICE NUMBER</strong></td>
            <td colspan='2' style='padding-bottom:5px;'>: TN/0090/25-26</td>
        </tr>
        <tr>
            <td class='border-left' colspan='1' rowspan='2'><strong>ADDRESS</strong></td>
            <td colspan='2' rowspan='2' style='padding-bottom:5px;'>: S.F.NO.287/1, BHARATHIPURAM, PAPPAMPATTI ROAD., KANNAMPALAYAM POST.,COIMBATORE</td>
          
            <td class='border-left' colspan='2' style='padding-bottom:5px;'><strong>DATE</strong></td>
            <td colspan='2'  style='padding-bottom:5px;'>: 5-May-25</td>
        </tr>
        <tr>
          
            <td class='border-left' colspan='2' style='padding-bottom:5px;'><strong>PLACE OF SERVICE</strong></td>
            <td colspan='2'  style='padding-bottom:5px;'>: TAMIL NADU</td>
        </tr>
        <tr>
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>  <strong>PAN</strong><br></td>
            <td colspan='2' style='padding-bottom:5px;'>: &nbsp;<br></td>
            
            <td class='border-left' colspan='2' style='padding-bottom:5px;'><strong>TERMS OF PAYMENT</strong></td>
            <td colspan='2' style='padding-bottom:5px;'>: 10 Days</td>
        </tr>
        <tr >
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>  <strong>GST No</strong><br></td>
            <td colspan='2' style='padding-bottom:5px;'>: &nbsp;</td>
            </td>
            <td class='border-left' colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
            <td colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
        </tr>
        <!-- GST Details -->
        <tr>
            <td class='border-left' colspan='1' style='padding-bottom:5px;'>

                <strong>State Code</strong>
            </td>
            <td colspan='2' style='padding-bottom:5px;'>: 33AVBPS9176R12M
            </td>
            <td class='border-left' colspan='2' style='padding-bottom:5px;'>
            &nbsp;
            </td>
            <td colspan='2' style='padding-bottom:5px;'>
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
        <tr>
            <td class='border-leftright'>1</td>
            <td class='no-border' colspan='3'>Parcel GST-TN Invoice for the month of Apr'25</td>
            <td class='no-border border-leftright'>12</td>
            <td class='no-border border-leftright'>996791</td>
            <td class='no-border text-right'>11,749.00</td>
        </tr>
        <tr>
            <td class='border-leftright'>2</td>
            <td class='no-border ' colspan='3' >TN SGST @ 6%</td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright text-right'>704.94</td>
        </tr>
        <tr>
            <td class='border-leftright'>3</td>
            <td class='no-border' colspan='3' >TN CGST @ 6%</td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright'></td>
            <td class='no-border border-leftright text-right'>704.94</td>
        </tr>
        <tr style='padding-bottom:100px;'>
            <td class='border-leftright border-bottom' style='padding-bottom:100px;'>4</td>
            <td class='no-border border-bottom' style='padding-bottom:100px;' colspan='3'>Rounded Off</td>
            <td class='no-border  border-leftright border-bottom' style='padding-bottom:100px;'></td>
            <td class='no-border  border-leftright border-bottom' style='padding-bottom:100px;'></td>
            <td class='no-border  border-leftright border-bottom text-right' style='padding-bottom:100px;'>0.12</td>
        </tr>


        <!-- Total Section -->
        <tr>
            <td class='no-border text-right border-bottom' colspan='6' style='font-size: 16px; font-weight: bold;'>T O T A L</td>
              <td class='no-border text-right border-bottom'>13,159.00</td>
        </tr>
        <tr>    
            <td class='no-border bold border-bottom'>Rupees</td>
            <td class='no-border border-bottom' colspan='6'>Thirteen Thousand One Hundred Fifty Nine Only</td>
          
        </tr>

        <!-- Bank Details -->
        <tr>
            <td class='no-border'colspan='4'><strong>Bank Name :</strong> RBL Bank Limited</td>
            <td class='no-border' colspan='3'><strong>Virtual AC No :</strong> VAPONPU000156</td>
        </tr>
        <tr>
            <td class='no-border border-bottom' colspan='4'><strong>IFSC :</strong> RATN0000113 </td>
            <td class='no-border border-bottom' colspan='3'><strong>Branch :</strong> T.NAGAR</td>
        </tr>
        <!-- Terms and Conditions -->
        <tr>
            <td class='no-border bold' colspan='7' style='padding-top: 5px;'>Terms & Conditions:</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>1. Interest @ 24% P.A will be levied on invoice amount if not paid within the Credit Period.</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>2. Cheque/DD/RTGS/NEFT should be made to M/s.SV Logistics</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>3. Any discrepancy should be notified to us in writing within 10 days from the invoice date, otherwise it will be presumed that the amount reflected on the bill is correct and have been verified at your end.</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>4. The owner must insure the goods against all risk.</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border' colspan='7'>5. Our responsibility ceases after the goods have been delivered to the carrier godown etc.</td>
        </tr>
        <tr class='row-line'>
            <td class='no-border border-bottom' colspan='7'>6. All claims and settlement are subject to Chennai Jurisdiction</td>
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
            <td colspan='7' style='text-align: center;' style='padding-bottom:40px;'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='7' style='text-align: center;'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='7' style='text-align: center;' style='padding-bottom:40px;'>&nbsp;</td>
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
$folderPath = "Bills/";

// Ensure directory exists
if (!is_dir($folderPath) && !mkdir($folderPath, 0777, true) && !is_dir($folderPath)) {
    die("Failed to create directory: " . $folderPath);
}

// Define PDF file path
$pdfPath = $folderPath . "hai" . ".pdf";
if (file_put_contents($pdfPath, $dompdf->output()) === false) {
    die("Failed to save PDF to " . $pdfPath);
}

?>