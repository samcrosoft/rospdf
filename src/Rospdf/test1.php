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
//$pdf->tempPath = "c:\\xampp\\tmp";
$pdf->tempPath = sys_get_temp_dir();

$pdf->ezSetMargins(20,20,20,20);
$pdf->openHere('Fit');

$pdf->selectFont('Helvetica');
for($i = 1; $i <= 2000; $i++){
    $pdf->ezText("Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol Lorem ipsum dol $i");
}

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
