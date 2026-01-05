<?php
// generadores/GeneradorSalidaPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorSalidaPDF {
    private $pdf;
    private $plantilla;
    
    public function __construct() {
        $posiblesRutas = array(
            __DIR__ . '/../../templates/salida.pdf',
            __DIR__ . '/../../templates/Salida.pdf',
            __DIR__ . '/../../templates/salidaBiene.pdf'
        );
        foreach ($posiblesRutas as $ruta) {
            if (file_exists($ruta)) {
                $this->plantilla = $ruta;
                break;
            }
        }
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida) {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0); 
        $this->pdf->SetAutoPageBreak(true, 10);
        
        $this->pdf->AddPage();
        $this->pdf->setSourceFile($this->plantilla);
        $templateId = $this->pdf->importPage(1);
        $this->pdf->useTemplate($templateId, 0, 0, null, null, true);
        
        $this->llenarDatos($trabajador, $bienes, $datosAdicionales);
        $this->pdf->Output($rutaSalida, 'F');
        return true;
    }
    
    private function llenarDatos($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->SetFont('Helvetica', '', 9);

        // Nombre (Coordenadas PdfFiller)
        $this->pdf->SetXY(23, 50);
        $this->pdf->Write(10, mb_strimwidth($trabajador->getNombre(), 0, 65, '...'));

        // Institución y Adscripción
        $this->pdf->SetXY(129, 52.5);
        $this->pdf->Write(5, mb_strimwidth($trabajador->getAdscripcion() ?: 'IMSS', 0, 50, '...'));
        $this->pdf->SetXY(38, 55.5);
        $this->pdf->Write(10, mb_strimwidth($trabajador->getAdscripcion(), 0, 50, '...'));

        // Matrícula, Identificación y Teléfono
        $this->pdf->SetXY(170, 57.5);
        $this->pdf->Write(6, $trabajador->getMatricula());
        $this->pdf->SetXY(44, 60.5);
        $this->pdf->Write(11, mb_strimwidth($trabajador->getIdentificacion(), 0, 45, '..'));
        $this->pdf->SetXY(167, 60.5);
        $this->pdf->Write(11, $trabajador->getTelefono());

        // Área de salida y Cantidad
        $this->pdf->SetXY(55, 73);
        $this->pdf->Write(10, $datosAdicionales['area_origen']);
        $cantidadTotal = 0;
        foreach($bienes as $b) { $cantidadTotal += $b['cantidad']; }
        $this->pdf->SetXY(30, 95);
        $this->pdf->Write(10, $cantidadTotal);

        // Naturaleza (Primer bien)
        if (!empty($bienes)) {
            $naturaleza = $bienes[0]['bien']->getNaturaleza();
            $this->pdf->SetXY(65, 95);
            $this->pdf->Write(10, $naturaleza);
            $this->marcarCasillaNaturaleza($naturaleza);
        }

        // Propósito y Estado
        $this->pdf->SetXY(42, 129);
        $this->pdf->Write(10, $datosAdicionales['destino']);
        $this->pdf->SetXY(115, 137);
        $this->pdf->Write(10, $datosAdicionales['observaciones'] ?: 'Bueno');

        // Devolución
        $this->marcarDevolucion($datosAdicionales['sujeto_devolucion'], $datosAdicionales['fecha_devolucion']);

        // Firmas y Responsables (Coordenadas fillForm1 y Common)
        $this->pdf->SetXY(199, 181);
        $this->pdf->Write(10, $datosAdicionales['matricula_autoriza']);
        $this->pdf->SetXY(120, 191);
        $this->pdf->Write(10, $trabajador->getNombre());

        // Fecha y Lugar
        $this->pdf->SetXY(195, 204); 
        $fecha = $this->formatFecha($datosAdicionales['lugar_fecha']);
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fecha);

        // Descripciones
        $x = 98; $y = 96;
        foreach (array_slice($bienes, 0, 5) as $item) {
            $this->pdf->SetXY($x, $y);
            $texto = $item['cantidad'] . " " . $item['bien']->getDescripcion();
            $this->pdf->MultiCell(100, 5, $texto, 0, 'L');
            $y = $this->pdf->GetY();
        }
    }

    private function marcarCasillaNaturaleza($nat) {
        $coords = array('BC' => array(14, 232), 'BMC' => array(14, 241), 'BMNC' => array(112, 232), 'BPS' => array(112, 241));
        if (isset($coords[$nat])) {
            $this->pdf->SetFillColor(173, 216, 230);
            $this->pdf->SetAlpha(0.5);
            $this->pdf->Rect($coords[$nat][0], $coords[$nat][1], 15, 5, 'F');
            $this->pdf->SetAlpha(1.0);
        }
    }

    private function marcarDevolucion($dev, $fecha) {
        if (strtolower($dev) == 'si') {
            $this->pdf->SetXY(77, 144); $this->pdf->Write(15, 'X');
            if ($fecha) { $this->pdf->SetXY(115, 146.5); $this->pdf->Write(10, $fecha); }
        } else {
            $this->pdf->SetXY(92, 144); $this->pdf->Write(15, 'X');
        }
    }

    private function formatFecha($f) {
        setlocale(LC_TIME, 'es_MX.UTF-8', 'spanish');
        return strftime("%d de %B de %Y", strtotime($f));
    }
}