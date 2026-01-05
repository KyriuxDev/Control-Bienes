<?php
// generadores/GeneradorPrestamoPDF.php

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
        
        // Adscripción Superior
        $this->pdf->SetXY(110, 20);  
        $this->pdf->Write(10, $trabajador->getAdscripcion()); 

        if (!empty($bienes)) {
            $nat = $bienes[0]['bien']->getNaturaleza();
            $this->pdf->SetXY(77, 51); $this->pdf->Write(10, $nat);
            $this->marcarCasillaForm3($nat);
        }

        $cant = 0; foreach($bienes as $b) { $cant += $b['cantidad']; }
        $this->pdf->SetXY(26, 51); $this->pdf->Write(10, $cant);
        $this->pdf->SetXY(68, 153); $this->pdf->Write(10, $datosAdicionales['nota'] ?: 'Bueno');

        // Responsables (Lado izquierdo y derecho)
        $this->pdf->SetXY(8, 194); $this->pdf->Write(10, $trabajador->getNombre());
        $this->pdf->SetXY(5, 203); $this->pdf->Write(10, $trabajador->getMatricula());

        $this->pdf->SetXY(108, 194); $this->pdf->Write(10, $datosAdicionales['matricula_autoriza']);
        $this->pdf->SetXY(110, 203); $this->pdf->Write(10, $datosAdicionales['matricula_administrativo']);

        // Fecha
        $this->pdf->SetXY(40, 216);
        setlocale(LC_TIME, 'es_MX.UTF-8', 'spanish');
        $fecha = strftime("%d de %B de %Y", strtotime($datosAdicionales['lugar_fecha']));
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fecha);

        // Lista de bienes (lado derecho)
        $y = 54;
        foreach ($bienes as $item) {
            $this->pdf->SetXY(120, $y);
            $this->pdf->Write(5, mb_strimwidth($item['bien']->getDescripcion(), 0, 45));
            $y += 4;
        }

        $this->pdf->Output($rutaSalida, 'F');
        return true;
    }

    private function marcarCasillaForm3($nat) {
        $coords = array('BC'=>array(3,231), 'BMC'=>array(3,235), 'BMNC'=>array(108,231), 'BPS'=>array(108,235));
        if(isset($coords[$nat])) {
            $this->pdf->SetFillColor(173, 216, 230); $this->pdf->SetAlpha(0.4);
            $this->pdf->Rect($coords[$nat][0], $coords[$nat][1], 13, 3, 'F');
            $this->pdf->SetAlpha(1.0);
        }
    }
}