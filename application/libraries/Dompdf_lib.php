<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Backward-compatible Dompdf wrapper used by existing report generators.
 *
 * Supports calls like:
 *   $this->load->library('dompdf_lib');
 *   $this->dompdf_lib->reset();
 *   $this->dompdf_lib->loadHtml($html);
 *   $this->dompdf_lib->setPaper('A4', 'landscape');
 *   $this->dompdf_lib->render();
 *   $output = $this->dompdf_lib->output();
 */
class Dompdf_lib
{
    /** @var Dompdf */
    private $dompdf;

    public function __construct()
    {
        require_once APPPATH . 'libraries/dompdf/autoload.inc.php';
        $this->reset();
    }

    public function reset()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isFontSubsettingEnabled', true);

        $this->dompdf = new Dompdf($options);
    }

    public function loadHtml($html)
    {
        $this->dompdf->loadHtml($html);
    }

    public function setPaper($size = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->setPaper($size, $orientation);
    }

    public function render()
    {
        $this->dompdf->render();
    }

    public function output()
    {
        return $this->dompdf->output();
    }

    public function stream($filename = 'document.pdf', $options = array())
    {
        $this->dompdf->stream($filename, $options);
    }
}
