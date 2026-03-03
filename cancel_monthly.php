<?php
$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
require_once('fpdf/fpdf.php');
require_once('fpdi/autoload.php'); // Make sure Composer installed it OR use full path if manual    
use setasign\Fpdi\Fpdi;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ackno2 = $_POST['ackno2'];

    // Hard delete
    $stmt = $conn->prepare("update monthlyreport set status='Cancelled' WHERE ackno = ?");
    $stmt->bind_param("s", $ackno2);

    if ($stmt->execute()) {
        class PDFWithRotation extends Fpdi
        {
            protected $angle = 0;
            protected $extgstates = [];

            function Rotate($angle, $x = -1, $y = -1)
            {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
                $this->angle = $angle;
                if ($angle != 0) {
                    $angle *= M_PI / 180;
                    $c = cos($angle);
                    $s = sin($angle);
                    $cx = $x * $this->k;
                    $cy = ($this->h - $y) * $this->k;
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy));
                }
            }

            function _endpage()
            {
                if ($this->angle != 0) {
                    $this->angle = 0;
                    $this->_out('Q');
                }
                parent::_endpage();
            }

            function SetAlpha($alpha, $bm = 'Normal')
            {
                $gs = $this->_getextgstate($alpha, $bm);
                $this->_setextgstate($gs);
            }

            function _getextgstate($alpha, $bm)
            {
                $gs = count($this->extgstates) + 1;
                $this->extgstates[$gs] = [
                    'ca' => $alpha,
                    'CA' => $alpha,
                    'BM' => '/' . $bm
                ];
                return $gs;
            }

            function _setextgstate($gs)
            {
                $this->_out(sprintf('/GS%d gs', $gs));
            }

            function _putextgstates()
            {
                foreach ($this->extgstates as $k => $extgstate) {
                    $this->_newobj();
                    $this->extgstates[$k]['n'] = $this->n;
                    $this->_out('<</Type /ExtGState');
                    $this->_out(sprintf('/ca %.3F', $extgstate['ca']));
                    $this->_out(sprintf('/CA %.3F', $extgstate['CA']));
                    $this->_out('/BM ' . $extgstate['BM']);
                    $this->_out('>>');
                    $this->_out('endobj');
                }
            }

            function _putresourcedict()
            {
                parent::_putresourcedict();
                if (!empty($this->extgstates)) {
                    $this->_out('/ExtGState <<');
                    foreach ($this->extgstates as $k => $extgstate) {
                        $this->_out('/GS' . $k . ' ' . $extgstate['n'] . ' 0 R');
                    }
                    $this->_out('>>');
                }
            }

            function _putresources()
            {
                $this->_putextgstates();
                parent::_putresources();
            }

            function _enddoc()
            {
                if (!empty($this->extgstates) && $this->PDFVersion < '1.4') {
                    $this->PDFVersion = '1.4';
                }
                parent::_enddoc();
            }
        }

        // === MAIN ===
        $pdf = new PDFWithRotation();
        $pdf->setSourceFile('./Monthly_bills/' . $ackno2 . '.pdf');

        $template = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($template);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($template);

        // 👇 Replace old watermark code with this 👇
        $watermarkText = 'CANCELLED';
        $pdf->SetFont('Arial', 'B', 100);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetAlpha(0.4);
        $pdf->Rotate(50, 105, 150); // (x, y) of rotation center
        $pdf->Text(-120, 310, 'CANCELLED'); // (x, y) of text start
        $pdf->Rotate(0); // Reset rotation

        $pdf->Output('F', './Monthly_bills/' . $ackno2 . '.pdf');




        echo "<script>alert('Invoice cancelled successfully.');  window.history.back();</script>";
    } else {
        echo "<script>alert('Failed to cancel invoice.'); window.history.back();</script>";
    }

    $stmt->close();
}
?>