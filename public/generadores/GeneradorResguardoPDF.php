<?php
// generadores/GeneradorResguardoPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorResguardoPDF {
    private $pdf;
    private $plantilla;
    
    public function __construct() {
        $this->plantilla = __DIR__ . '/../../templates/resguardo.pdf';
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida) {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false); $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0);
        
        $this->pdf->AddPage();
        $this->pdf->setSourceFile($this->plantilla);
        $templateId = $this->pdf->importPage(1);
        $this->pdf->useTemplate($templateId, 0, 0, null, null, true);
        
        $this->pdf->SetFont('helvetica', '', 7);
        setlocale(LC_TIME, 'es_MX.UTF-8', 'spanish');
        $fecha = strftime("%d de %B de %Y", strtotime($datosAdicionales['lugar_fecha']));

        // Fecha y Folio (Superiores)
        $this->pdf->SetXY(111.9, 55); 
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fecha);
        $this->pdf->SetXY(176, 54);
        $this->pdf->Write(10, $datosAdicionales['folio']); 

        $this->pdf->SetFont('helvetica', '', 8);

        // Datos del Resguardante
        $this->pdf->SetXY(55, 71);
        $this->pdf->Write(10, $trabajador->getAdscripcion()); 
        $this->pdf->SetXY(55, 76);
        $this->pdf->Write(8, $trabajador->getNombre()); 
        $this->pdf->SetXY(55, 80);   
        $this->pdf->Write(8, $trabajador->getCargo()); 

        $cant = 0; foreach($bienes as $b) { $cant += $b['cantidad']; }
        $this->pdf->SetXY(35, 110); $this->pdf->Write(8, $cant);

        $this->pdf->SetXY(55, 84); $this->pdf->Write(8, $trabajador->getAdscripcion());
        $this->pdf->SetXY(55, 88); $this->pdf->Write(8, $trabajador->getTelefono()); 

        // Firmas
        $this->pdf->SetXY(28, 228); $this->pdf->Write(8, $trabajador->getNombre()); 
        $this->pdf->SetXY(20, 232); $this->pdf->Write(8, $trabajador->getCargo()); 

        $this->pdf->SetXY(118, 228);
        $this->pdf->Write(10, $datosAdicionales['entrega_resguardo']);
        $this->pdf->SetXY(111, 231);   
        $this->pdf->Write(10, $datosAdicionales['cargo_entrega']); 

        // Descripciones agrupadas
        $desc = array(); $marcas = array(); $series = array();
        foreach($bienes as $i) {
            $desc[] = $i['bien']->getDescripcion();
            $marcas[] = $i['bien']->getMarca();
            $series[] = $i['bien']->getSerie();
        }
        $this->pdf->SetXY(128, 109); $this->pdf->Write(5, implode(', ', $desc));
        $this->pdf->SetXY(128, 116); $this->pdf->Write(8, implode(', ', $marcas));
        $this->pdf->SetXY(128, 121); $this->pdf->Write(13, implode(', ', $series));

        $this->pdf->Output($rutaSalida, 'F');
        return true;
    }
}