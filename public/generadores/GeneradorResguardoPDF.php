<?php
// public/generadores/GeneradorResguardoPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorResguardoPDF {
    private $pdf;
    private $plantilla;
    
    public function __construct() {
        $this->plantilla = __DIR__ . '/../../templates/resguardo.pdf';
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
        
        // Parsear la fecha recibida (YYYY-MM-DD)
        $timestamp = strtotime($fechaStr . ' 12:00:00');
        
        $dia = date('j', $timestamp);
        $mesNumero = date('n', $timestamp);
        $mesTexto = $meses[$mesNumero];
        $anio = date('Y', $timestamp);
        
        return $lugar . ", " . $dia . " de " . $mesTexto . " de " . $anio;
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida) {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false); 
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->AddPage();
        
        $this->pdf->setSourceFile($this->plantilla);
        $this->pdf->useTemplate($this->pdf->importPage(1), 0, 0, null, null, true);
        
        $this->llenarDatos($trabajador, $bienes, $datosAdicionales);

        if (count($bienes) > 2) {
            $this->generarAnexo($trabajador, $bienes, $datosAdicionales);
        }

        $this->pdf->Output($rutaSalida, 'F');
    }
    
    private function llenarDatos($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->SetTextColor(0, 0, 0);

        // FECHA - Usar la fecha del formulario
        $lugar = isset($datosAdicionales['lugar']) ? $datosAdicionales['lugar'] : 'Oaxaca de Juárez, Oaxaca';
        
        if (isset($datosAdicionales['fecha'])) {
            $fechaFormateada = $this->generarTextoFecha($datosAdicionales['fecha'], $lugar);
        } else {
            $fechaFormateada = $this->generarTextoFecha(date('Y-m-d'), $lugar);
        }
        
        $this->pdf->SetXY(112, 54.5); 
        $this->pdf->Write(8, $fechaFormateada);
        
        // FOLIO
        $this->pdf->SetXY(176, 53.5);
        $this->pdf->Write(10, $datosAdicionales['folio']); 

        // DATOS TRABAJADOR
        $this->pdf->SetXY(55, 72); 
        $this->pdf->Write(8, $trabajador->getInstitucion());
        
        $this->pdf->SetXY(55, 76); 
        $this->pdf->Write(8, $trabajador->getNombre()); 
        
        $this->pdf->SetXY(55, 80); 
        $this->pdf->Write(8, $trabajador->getCargo()); 
        
        $this->pdf->SetXY(55, 84); 
        $this->pdf->Write(8, $trabajador->getAdscripcion());
        
        $this->pdf->SetXY(55, 88); 
        $this->pdf->Write(8, $trabajador->getTelefono()); 

        // CUERPO - Escribir bienes en la tabla del formulario
        $this->escribirBienesEnTabla($bienes);

        // FIRMAS - Primera página
        // Quien RECIBE (Resguardante - Izquierda)
        $this->pdf->SetXY(28, 228); 
        $this->pdf->Write(8, $trabajador->getNombre()); 
        
        $this->pdf->SetXY(20, 232); 
        $this->pdf->Write(8, $trabajador->getCargo());
        
        // Quien ENTREGA (Derecha)
        $this->pdf->SetXY(118, 228); 
        $this->pdf->Write(10, $datosAdicionales['entrega_resguardo']);
        
        $this->pdf->SetXY(111, 232); 
        $this->pdf->Write(10, $datosAdicionales['cargo_entrega']); 
    }

    /**
     * Escribe los bienes en la tabla del formulario (máximo 2 bienes en primera página)
     */
    private function escribirBienesEnTabla($bienes) {
        if (count($bienes) > 2) {
            // Si hay más de 2 bienes, mostrar mensaje de anexo
            $this->pdf->SetXY(138, 109);
            $this->pdf->Write(5, "VER ANEXO ADJUNTO ( " . count($bienes) . " PARTIDAS )");
            return;
        }

        // Escribir hasta 2 bienes en la tabla
        $yInicio = 109; // Posición Y inicial de la primera fila
        $altoFila = 23; // Alto de cada fila en la tabla
        
        foreach (array_slice($bienes, 0, 2) as $index => $item) {
            $bien = $item['bien'];
            $cantidad = isset($item['cantidad']) ? $item['cantidad'] : 1;
            $yActual = $yInicio + ($index * $altoFila);
            
            // Columna: CANTIDAD (O IDENTIFICACIÓN)
            $this->pdf->SetXY(26, $yActual + 6);
            $this->pdf->Write(5, $cantidad);
            
            // Columna: NATURALEZA
            $this->pdf->SetXY(78, $yActual + 6);
            $this->pdf->Write(5, $bien->getNaturaleza());
            
            // Columna: DESCRIPCIÓN
            $descripcion = $bien->getDescripcion();
            if (strlen($descripcion) > 35) {
                $descripcion = substr($descripcion, 0, 32) . '...';
            }
            $this->pdf->SetXY(128, $yActual);
            $this->pdf->Write(5, $descripcion);
            
            // MARCA
            $this->pdf->SetXY(128, $yActual + 6);
            $this->pdf->Write(5, $bien->getMarca());
            
            // SERIE
            $this->pdf->SetXY(128, $yActual + 11);
            $this->pdf->Write(5, $bien->getSerie());
        }
    }

    private function generarAnexo($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->AddPage();
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetAutoPageBreak(true, 50);

        // ENCABEZADO
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 10, "ANEXO DE RESGUARDO DE BIENES", 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, "FOLIO: " . $datosAdicionales['folio'], 0, 1, 'C');
        $this->pdf->Ln(8);

        // TABLA DE BIENES
        $this->pdf->SetFillColor(235, 235, 235);
        $this->pdf->SetFont('helvetica', '', 8);
        
        $w = array(15, 30, 50, 30, 30, 25); 
        
        $this->pdf->Cell($w[0], 7, 'CANT.', 1, 0, 'C', 1);
        $this->pdf->Cell($w[1], 7, 'NATURALEZA', 1, 0, 'C', 1);
        $this->pdf->Cell($w[2], 7, 'DESCRIPCIÓN', 1, 0, 'C', 1);
        $this->pdf->Cell($w[3], 7, 'MARCA', 1, 0, 'C', 1);
        $this->pdf->Cell($w[4], 7, 'MODELO', 1, 0, 'C', 1);
        $this->pdf->Cell($w[5], 7, 'SERIE', 1, 1, 'C', 1);

        $this->pdf->SetFont('helvetica', '', 7);
        foreach ($bienes as $item) {
            $bien = $item['bien'];
            $cant = isset($item['cantidad']) ? $item['cantidad'] : '1';

            $nb = $this->pdf->getNumLines($bien->getDescripcion(), $w[2]);
            $h = 5 * $nb;
            if($h < 7) {
                $h = 7;
            }

            $this->pdf->MultiCell($w[0], $h, $cant, 1, 'C', 0, 0);
            $this->pdf->MultiCell($w[1], $h, $bien->getNaturaleza(), 1, 'C', 0, 0);
            $this->pdf->MultiCell($w[2], $h, $bien->getDescripcion(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[3], $h, $bien->getMarca(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[4], $h, $bien->getModelo(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[5], $h, $bien->getSerie(), 1, 'L', 0, 1);
        }

        // BLOQUE DE FIRMAS
        $this->pdf->Ln(20);
        $yFirmas = $this->pdf->GetY();
        
        if ($yFirmas > 230) {
            $this->pdf->AddPage();
            $yFirmas = 40;
        }

        $this->pdf->SetFont('helvetica', '', 8);
        
        // Columna Izquierda: Resguardante
        $this->pdf->SetXY(15, $yFirmas);
        $this->pdf->Cell(80, 0, '', 'T', 0, 'C');
        
        $this->pdf->SetXY(15, $yFirmas + 2);
        $this->pdf->Cell(80, 4, "RESGUARDANTE", 0, 1, 'C');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', '', 7.5);
        $this->pdf->Cell(80, 4, $trabajador->getNombre(), 0, 1, 'C');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', '', 6.5);
        $this->pdf->MultiCell(80, 3, $trabajador->getCargo(), 0, 'C');

        // Columna Derecha: Entrega
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetXY(115, $yFirmas);
        $this->pdf->Cell(80, 0, '', 'T', 0, 'C');
        
        $this->pdf->SetXY(115, $yFirmas + 2);
        $this->pdf->Cell(80, 4, "ENTREGA", 0, 1, 'C');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', '', 7.5);
        $this->pdf->Cell(80, 4, $datosAdicionales['entrega_resguardo'], 0, 1, 'C');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', '', 6.5);
        $cargoEntrega = isset($datosAdicionales['cargo_entrega']) ? $datosAdicionales['cargo_entrega'] : "DEPARTAMENTO DE BIENES Y SUMINISTROS";
        $this->pdf->MultiCell(80, 3, $cargoEntrega, 0, 'C');
    }
}