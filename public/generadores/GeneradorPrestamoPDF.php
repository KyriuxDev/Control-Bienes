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
    
    /**
     * Genera la fecha exacta usando la fecha recibida del formulario
     */
    private function generarTextoFecha($fechaStr, $lugar) {
        $meses = array(
            "1" => "enero", "2" => "febrero", "3" => "marzo", 
            "4" => "abril", "5" => "mayo", "6" => "junio", 
            "7" => "julio", "8" => "agosto", "9" => "septiembre", 
            "10" => "octubre", "11" => "noviembre", "12" => "diciembre"
        );
        
        $timestamp = strtotime($fechaStr . ' 12:00:00');
        
        $dia = date('j', $timestamp);
        $mesNumero = date('n', $timestamp);
        $mesTexto = $meses[$mesNumero];
        $anio = date('Y', $timestamp);
        
        return $lugar . ", " . $dia . " de " . $mesTexto . " de " . $anio;
    }
    
    /**
     * Genera fecha corta sin lugar (para fecha de devolución)
     * Formato: "30 de enero de 2025"
     */
    private function generarTextoFechaCorta($fechaStr) {
        $meses = array(
            "1" => "enero", "2" => "febrero", "3" => "marzo", 
            "4" => "abril", "5" => "mayo", "6" => "junio", 
            "7" => "julio", "8" => "agosto", "9" => "septiembre", 
            "10" => "octubre", "11" => "noviembre", "12" => "diciembre"
        );
        
        $timestamp = strtotime($fechaStr . ' 12:00:00');
        
        $dia = date('j', $timestamp);
        $mesNumero = date('n', $timestamp);
        $mesTexto = $meses[$mesNumero];
        $anio = date('Y', $timestamp);
        
        return $dia . " de " . $mesTexto . " de " . $anio;
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida) {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false); 
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->AddPage();
        
        $this->pdf->setSourceFile($this->plantilla);
        $templateId = $this->pdf->importPage(1);
        $this->pdf->useTemplate($templateId, 0, 0, null, null, true);
        
        $this->llenarDatos($trabajador, $bienes, $datosAdicionales);

        if (count($bienes) > 10) {
            $this->generarAnexo($trabajador, $bienes, $datosAdicionales);
        }

        $this->pdf->Output($rutaSalida, 'F');
    }
    
    private function llenarDatos($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetTextColor(0, 0, 0);

        // DEPARTAMENTO/ADSCRIPCIÓN (aparece dos veces)
        $departamento = isset($datosAdicionales['departamento_per']) ? $datosAdicionales['departamento_per'] : $trabajador->getAdscripcion();
        $this->pdf->SetXY(110, 20); 
        $this->pdf->Write(10, $departamento);
        
        $this->pdf->SetXY(110, 167); 
        $this->pdf->Write(10, $departamento);
        
        // DÍAS DE PRÉSTAMO (después del segundo "Coordinación de Informática")
        if (isset($datosAdicionales['dias_prestamo']) && $datosAdicionales['dias_prestamo'] > 0) {
            $this->pdf->SetFont('helvetica', '', 8); // Negrita tamaño 8
            $this->pdf->SetXY(86, 167); // Justo debajo del departamento
            $diasTexto = $datosAdicionales['dias_prestamo'] . ' día' . ($datosAdicionales['dias_prestamo'] != 1 ? 's' : '');
            $this->pdf->Write(5, $diasTexto);
            $this->pdf->SetFont('helvetica', '', 8); // Restaurar fuente normal
        }
        

        // NATURALEZA Y CANTIDAD
        if (!empty($bienes)) {
            $naturaleza = $bienes[0]['bien']->getNaturaleza();
            $this->pdf->SetXY(77, 51); 
            $this->pdf->Write(10, $naturaleza);
            $this->marcarCasillaNaturaleza($naturaleza);
        }

        $cantidadTotal = 0;
        foreach($bienes as $b) {
            $cantidadTotal += isset($b['cantidad']) ? $b['cantidad'] : 1;
        }
        $this->pdf->SetXY(26, 51); 
        $this->pdf->Write(10, $cantidadTotal);

        // ESTADO DE LOS BIENES
        $estado = isset($datosAdicionales['estado_general']) ? $datosAdicionales['estado_general'] : 'Bueno';
        $this->pdf->SetXY(68, 153);
        $this->pdf->Write(10, $estado);

        // DESCRIPCIONES DE BIENES (lado derecho, máximo 10 en primera página)
        $this->escribirDescripcionesBienes($bienes);

        // FIRMAS - Primera página
        // Quien RECIBE (Izquierda)
        $this->pdf->SetXY(8, 194); 
        $this->pdf->Write(10, $trabajador->getNombre());
        
        $this->pdf->SetXY(5, 203); 
        $this->pdf->Write(10, $trabajador->getMatricula());

        // Quien ENTREGA (Derecha - Control Administrativo)
        $responsableAdministrativo = isset($datosAdicionales['responsable_control_administrativo']) 
            ? $datosAdicionales['responsable_control_administrativo'] 
            : $trabajador->getNombre();
        $this->pdf->SetXY(108, 194); 
        $this->pdf->Write(10, $responsableAdministrativo);

        $matriculaAdmin = isset($datosAdicionales['matricula_administrativo']) 
            ? $datosAdicionales['matricula_administrativo'] 
            : $trabajador->getMatricula();
        $this->pdf->SetXY(110, 203); 
        $this->pdf->Write(10, $matriculaAdmin);

        // FECHA Y LUGAR
        $lugar = isset($datosAdicionales['lugar']) ? $datosAdicionales['lugar'] : 'Oaxaca de Juárez, Oaxaca';
        $fechaFormateada = $this->generarTextoFecha($datosAdicionales['fecha'], $lugar);
        
        $this->pdf->SetXY(40, 216);
        $this->pdf->Write(8, $fechaFormateada);
    }

    private function escribirDescripcionesBienes($bienes) {
        $this->pdf->SetFont('helvetica', '', 8);
        $yPos = 54;
        
        if (count($bienes) > 10) {
            $this->pdf->SetXY(120, $yPos);
            $this->pdf->Write(5, 'Ver Anexo Adjunto (' . count($bienes) . ' partidas)');
            return;
        }

        // Mostrar hasta 10 bienes
        foreach (array_slice($bienes, 0, 10) as $item) {
            $bien = $item['bien'];
            $descripcion = $bien->getDescripcion();
            
            // Limitar descripción a 45 caracteres
            if (strlen($descripcion) > 45) {
                $descripcion = substr($descripcion, 0, 42) . '...';
            }
            
            $this->pdf->SetXY(120, $yPos);
            $this->pdf->Write(5, $descripcion);
            $yPos += 4;
        }
    }

    private function marcarCasillaNaturaleza($naturaleza) {
        $coords = array(
            'BC' => array(3, 231),
            'BMC' => array(3, 235),
            'BMNC' => array(108, 231),
            'BPS' => array(108, 235)
        );
        
        if (isset($coords[$naturaleza])) {
            $this->pdf->SetFillColor(173, 216, 230);
            $this->pdf->SetAlpha(0.4);
            $this->pdf->Rect($coords[$naturaleza][0], $coords[$naturaleza][1], 13, 3, 'F');
            $this->pdf->SetAlpha(1.0);
        }
    }

    private function generarAnexo($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->AddPage();
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetAutoPageBreak(true, 50);

        // ENCABEZADO
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 10, "ANEXO - VALE DE PRÉSTAMO", 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, "Trabajador: " . $trabajador->getNombre(), 0, 1, 'C');
        $this->pdf->Ln(8);

        // TABLA DE BIENES
        $this->pdf->SetFillColor(235, 235, 235);
        $this->pdf->SetFont('helvetica', '', 8);
        
        $w = array(15, 65, 34, 33, 33);
        
        $this->pdf->Cell($w[0], 7, 'CANT.', 1, 0, 'C', 1);
        $this->pdf->Cell($w[1], 7, 'DESCRIPCIÓN', 1, 0, 'C', 1);
        $this->pdf->Cell($w[2], 7, 'MARCA', 1, 0, 'C', 1);
        $this->pdf->Cell($w[3], 7, 'MODELO', 1, 0, 'C', 1);
        $this->pdf->Cell($w[4], 7, 'SERIE', 1, 1, 'C', 1);

        $this->pdf->SetFont('helvetica', '', 7);
        foreach ($bienes as $item) {
            $bien = $item['bien'];
            $cant = isset($item['cantidad']) ? $item['cantidad'] : '1';

            $nb = $this->pdf->getNumLines($bien->getDescripcion(), $w[1]);
            $h = 5 * $nb;
            if($h < 7) $h = 7;

            $this->pdf->MultiCell($w[0], $h, $cant, 1, 'C', 0, 0);
            $this->pdf->MultiCell($w[1], $h, $bien->getDescripcion(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[2], $h, $bien->getMarca(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[3], $h, $bien->getModelo(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[4], $h, $bien->getSerie(), 1, 'L', 0, 1);
        }

        // FIRMAS
        $this->pdf->Ln(20);
        $yFirmas = $this->pdf->GetY();
        
        if ($yFirmas > 230) {
            $this->pdf->AddPage();
            $yFirmas = 40;
        }

        $this->pdf->SetFont('helvetica', '', 8);
        
        // Quien RECIBE (Izquierda)
        $this->pdf->SetXY(15, $yFirmas);
        $this->pdf->Cell(80, 0, '', 'T', 0, 'C');
        
        $this->pdf->SetXY(15, $yFirmas + 2);
        $this->pdf->Cell(80, 4, "RECIBE EN PRÉSTAMO", 0, 1, 'C');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', '', 7.5);
        $this->pdf->Cell(80, 4, $trabajador->getNombre(), 0, 1, 'C');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', '', 6.5);
        $this->pdf->MultiCell(80, 3, $trabajador->getCargo(), 0, 'C');

        // Quien ENTREGA (Derecha)
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY(115, $yFirmas);
        $this->pdf->Cell(80, 0, '', 'T', 0, 'C');
        
        $this->pdf->SetXY(115, $yFirmas + 2);
        $this->pdf->Cell(80, 4, "ENTREGA", 0, 1, 'C');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', '', 7.5);
        $responsable = isset($datosAdicionales['responsable_control_administrativo']) 
            ? $datosAdicionales['responsable_control_administrativo'] 
            : $trabajador->getNombre();
        $this->pdf->Cell(80, 4, $responsable, 0, 1, 'C');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', '', 6.5);
        $this->pdf->MultiCell(80, 3, "CONTROL ADMINISTRATIVO", 0, 'C');
    }
}