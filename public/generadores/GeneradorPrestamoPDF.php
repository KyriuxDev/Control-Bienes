<?php
// public/generadores/GeneradorPrestamoPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorPrestamoPDF {
    private $pdf;
    private $plantilla;
    
    public function __construct() {
        $this->plantilla = __DIR__ . '/../../templates/prestamo.pdf';
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida) {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false); $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0);
        
        $this->pdf->AddPage();
        $this->pdf->setSourceFile($this->plantilla);
        $templateId = $this->pdf->importPage(1);
        $this->pdf->useTemplate($templateId, 0, 0, null, null, true);
        
        $this->pdf->SetFont('helvetica', '', 8);
        
        // Departamento (Coordenadas fillForm3)
        $this->pdf->SetXY(110, 20); $this->pdf->Write(10, $datosAdicionales['departamento_per']); 
        $this->pdf->SetXY(110, 167); $this->pdf->Write(10, $datosAdicionales['departamento_per']);

        if (!empty($bienes)) {
            $nat = $bienes[0]['bien']->getNaturaleza();
            $this->pdf->SetXY(77, 51); $this->pdf->Write(10, $nat);
            $this->marcarCasillaNature($nat);
        }

        $cant = 0; foreach($bienes as $b) { $cant += $b['cantidad']; }
        $this->pdf->SetXY(26, 51); $this->pdf->Write(10, $cant);

        // Firmas y Matrículas
        $this->pdf->SetXY(8, 194); $this->pdf->Write(10, $trabajador->getNombre());
        $this->pdf->SetXY(5, 203); $this->pdf->Write(10, $trabajador->getMatricula());

        $this->pdf->SetXY(108, 194); $this->pdf->Write(10, $trabajador->getNombre()); // Nombre que recibe
        $this->pdf->SetXY(110, 203); $this->pdf->Write(10, $datosAdicionales['matricula_administrativo']);

        // Fecha inferior
        $this->pdf->SetXY(40, 216);
        $this->pdf->Write(8, "Oaxaca de Juárez, Oax. a " . date('d/m/Y', strtotime($datosAdicionales['lugar_fecha'])));

        // Lista de bienes (lado derecho)
        $y = 54;
        foreach (array_slice($bienes, 0, 10) as $item) {
            $this->pdf->SetXY(120, $y);
            $this->pdf->Write(5, mb_strimwidth($item['bien']->getDescripcion(), 0, 45));
            $y += 4;
        }

        $this->pdf->Output($rutaSalida, 'F');
        return true;
    }

    private function marcarCasillaNature($nat) {
        $coords = array('BC'=>array(3,231), 'BMC'=>array(3,235), 'BMNC'=>array(108,231), 'BPS'=>array(108,235));
        if(isset($coords[$nat])) {
            $this->pdf->SetFillColor(173, 216, 230); $this->pdf->SetAlpha(0.4);
            $this->pdf->Rect($coords[$nat][0], $coords[$nat][1], 13, 3, 'F');
            $this->pdf->SetAlpha(1.0);
        }
    }
}