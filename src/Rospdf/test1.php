<?php
function __autoload($class)
{
    $parts = explode('\\', $class);
    require end($parts) . '.php';
}
//namespace Rospdf\src;
/**
 * Created by PhpStorm.
 * User: Adebola
 * Date: 14/03/14
 * Time: 11:18
 */
error_reporting(E_ALL);
set_time_limit(1800);
//set_include_path('../src/' . PATH_SEPARATOR . get_include_path());
//include("../src/Cezpdf.php");

$start = microtime(true);
//$oPdf = new ();

use Rospdf\Cezpdf;

class CReportTest extends Cezpdf{
    /**
     * constructor placed here
     * @param $p
     * @param $o
     */
    function __construct($p,$o){
        parent::__construct($p, $o,'none',array());
    }
}
$pdf = new CReportTest('a4','portrait');

$pdf->ezSetMargins(20,20,20,20);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
for($i = 1; $i <= 200; $i++){
    $pdf->ezText("Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol $i");
}

$pdf->ezNewPage();
$pdf->ezText("Some Sample Graph/Charting Samples ");
$pdf->setColor(0, 0, 255);
$pdf->filledEllipse(300,$pdf->y-105,40,0,0, 16,0,165);
$pdf->setColor(255, 0,0 );
$pdf->filledEllipse(300,$pdf->y-105,40,0,0, 16,165,360);
//$pdf->setColor(128,128,128 );
//$pdf->filledEllipse(300,$pdf->y-105,40,0,0, 16,270,360);
$pdf->setColor(255, 255, 255);
$pdf->filledEllipse(300,$pdf->y-105,30,0,0, 16,0,360);
$pdf->setColor(0, 0,0);
$pdf->ezSetY($pdf->y-85);
$pdf->ezText("22%",25, array(
    'aleft' => 275,
    'right' => 90
));


// try the filled sector
$xc = 205;
$yc = 600;
$r = 50;

$pdf->setColor(0, 0, 255);
$pdf->PieSector($xc, $yc, $r, 20, 120, 'F', false, 0, 2);

$pdf->setColor(0, 255, 0);
$pdf->PieSector($xc, $yc, $r, 120, 250, 'F', false, 0, 2);

$pdf->setColor(128, 255, 0);
$pdf->PieSector($xc, $yc, $r, 250, 310, 'F', false, 0, 2);

$pdf->setColor(255, 0, 0);
$pdf->PieSector($xc, $yc, $r, 310, 20, 'F', false, 0, 2);


$pdf->setColor(153,0,0);
//$pdf->setLineStyle(1,'','');
$pdf->setLineStyle(1,'','', array(2,1));
$pdf->RoundedRect(100,210, 150, 80, 30, '0101', 'DF');


if (isset($_GET['d']) && $_GET['d']){
    $pdfcode = $pdf->ezOutput(1);
    $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
    echo '<html><body>';
    echo trim($pdfcode);
    echo '</body></html>';
} else {
    $pdf->ezStream(array('compress'=>0));
}

$end = microtime(true) - $start;
//error_log($end . ' o');
