<?php defined('BASEPATH') or exit('No direct script access allowed');

// Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
    public function __construct()
    {
        require_once dirname(__FILE__) . '/dompdf/autoload.inc.php';
        $pdf = new DOMPDF();
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $options->set('isFontSubsettingEnabled', true);
        $pdf->setOptions($options);
        $CI = &get_instance();
        $CI->dompdf = $pdf;
    }
}
