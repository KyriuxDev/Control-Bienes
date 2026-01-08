<?php
// public/generadores/GeneradorSalidaPDF.php - VERSIÓN CON CAMPO INDEPENDIENTE PARA CONSTANCIA

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorSalidaPDF {
    private $pdf;
    private $plantilla;
    
    public function __construct() {
        $this->plantilla = __DIR__ . '/../../templates/salidaBien.pdf';
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
    
    /**
     * Obtiene todas las naturalezas únicas de los bienes
     */
    private function obtenerNaturalezasUnicas($bienes) {
        $naturalezas = array();
        foreach ($bienes as $item) {
            $naturaleza = $item['bien']->getNaturaleza();
            if (!in_array($naturaleza, $naturalezas)) {
                $naturalezas[] = $naturaleza;
            }
        }
        return $naturalezas;
    }
    
    /**
     * Marca (subraya) todas las casillas de naturaleza presentes en los bienes
     */
    private function marcarCasillasNaturaleza($naturalezas) {
        $coords = array(
            'BC' => array(14, 232),
            'BMC' => array(14, 241),
            'BMNC' => array(112, 232),
            'BPS' => array(112, 241)
        );
        
        $this->pdf->SetFillColor(173, 216, 230);
        $this->pdf->SetAlpha(0.5);
        
        foreach ($naturalezas as $naturaleza) {
            if (isset($coords[$naturaleza])) {
                $this->pdf->Rect($coords[$naturaleza][0], $coords[$naturaleza][1], 15, 5, 'F');
            }
        }
        
        $this->pdf->SetAlpha(1.0);
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

        // Generar anexo si hay MÁS DE 5 bienes
        if (count($bienes) > 5) {
            $this->generarAnexo($trabajador, $bienes, $datosAdicionales);
        }

        $this->pdf->Output($rutaSalida, 'F');
    }
    
    private function llenarDatos($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(0, 0, 0);

        // NOMBRE DEL TRABAJADOR
        $this->pdf->SetXY(23, 50);
        $nombre_limitado = mb_strimwidth($trabajador->getNombre(), 0, 65, '...');
        $this->pdf->Write(10, $nombre_limitado);

        // INSTITUCIÓN
        $this->pdf->SetXY(129, 52.5);
        $institucion_limitado = mb_strimwidth($trabajador->getInstitucion(), 0, 50, '...');
        $this->pdf->Write(5, $institucion_limitado);

        // ADSCRIPCIÓN
        $this->pdf->SetXY(38, 55.5);
        $adscripcion_limitado = mb_strimwidth($trabajador->getAdscripcion(), 0, 50, '...');
        $this->pdf->Write(10, $adscripcion_limitado);

        // MATRÍCULA
        $this->pdf->SetXY(170, 57.5);
        $this->pdf->Write(6, $trabajador->getMatricula());

        // IDENTIFICACIÓN
        $this->pdf->SetXY(44, 60.5);
        $identificacion_limitado = mb_strimwidth($trabajador->getIdentificacion(), 0, 45, '..');
        $this->pdf->Write(11, $identificacion_limitado);

        // TELÉFONO
        $this->pdf->SetXY(167, 60.5);
        $this->pdf->Write(11, $trabajador->getTelefono());

        // ÁREA DE SALIDA
        $area = isset($datosAdicionales['area']) ? $datosAdicionales['area'] : 'Coordinación de Informática';
        $this->pdf->SetXY(55, 73);
        $this->pdf->Write(10, $area);

        // MARCAR TODAS LAS NATURALEZAS ÚNICAS
        $naturalezasUnicas = $this->obtenerNaturalezasUnicas($bienes);
        $this->marcarCasillasNaturaleza($naturalezasUnicas);

        // PROPÓSITO DEL BIEN
        $proposito = isset($datosAdicionales['proposito']) ? $datosAdicionales['proposito'] : 'Uso institucional';
        $this->pdf->SetXY(42, 129);
        $this->pdf->Write(10, $proposito);

        // ESTADO DE LOS BIENES - USAR ESTADO GLOBAL
        $estadoMostrar = isset($datosAdicionales['estado_general']) ? $datosAdicionales['estado_general'] : 'Buenas condiciones';
        
        $this->pdf->SetXY(115, 137);
        $this->pdf->Write(10, $estadoMostrar);

        // DEVOLUCIÓN (SI/NO) - USAR OPCIÓN GLOBAL
        $sujetoDevolucionGlobal = isset($datosAdicionales['sujeto_devolucion_global']) ? $datosAdicionales['sujeto_devolucion_global'] : 0;
        
        if ($sujetoDevolucionGlobal == 1) {
            // Marcar SÍ
            $this->pdf->SetXY(77, 144);
            $this->pdf->Write(15, 'X');
            
            // FECHA DE DEVOLUCIÓN FORMATEADA - USAR CAMPO ESPECÍFICO DE CONSTANCIA
            if (isset($datosAdicionales['fecha_devolucion_constancia']) && !empty($datosAdicionales['fecha_devolucion_constancia'])) {
                $fechaDevolucionFormateada = $this->generarTextoFechaCorta($datosAdicionales['fecha_devolucion_constancia']);
                $this->pdf->SetFont('helvetica', '', 7);
                $this->pdf->SetXY(111, 149);
                $this->pdf->Write(5, $fechaDevolucionFormateada);
                $this->pdf->SetFont('helvetica', '', 9);
            }
        } else {
            // Marcar NO
            $this->pdf->SetXY(92, 144);
            $this->pdf->Write(15, 'X');
        }

        // DESCRIPCIONES DE BIENES
        $this->escribirDescripcionesBienes($bienes);

        // RESPONSABLE QUE ENTREGA
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetXY(10, 191);
        $this->pdf->Write(10, $trabajador->getNombre());

        // RESPONSABLE DE LA ENTREGA (Firma)
        $this->pdf->SetXY(120, 191);
        $responsableEntrega = isset($datosAdicionales['entrega_resguardo']) ? $datosAdicionales['entrega_resguardo'] : $trabajador->getNombre();
        $this->pdf->Write(10, $responsableEntrega);

        // FECHA Y LUGAR
        $lugar = isset($datosAdicionales['lugar']) ? $datosAdicionales['lugar'] : 'Oaxaca de Juárez, Oaxaca';
        $fechaFormateada = $this->generarTextoFecha($datosAdicionales['fecha'], $lugar);
        
        $this->pdf->SetXY(10, 212);
        $this->pdf->Write(8, $fechaFormateada);
    }

    private function escribirDescripcionesBienes($bienes) {
        $this->pdf->SetFont('helvetica', '', 7);
        
        $y = 98;
        $width = 100;
        $lineHeight = 5;

        if (count($bienes) > 5) {
            $this->pdf->SetXY($x, $y + 2);
            $this->pdf->SetFont('helvetica', '', 7);
            $this->pdf->MultiCell($width, $lineHeight, 'Ver Anexo Adjunto (' . count($bienes) . ' partidas)', 0, 'L');
            return;
        }

        // Mostrar hasta 5 bienes - CADA BIEN EN SU PROPIA LÍNEA
        $primeros = array_slice($bienes, 0, 5);
        foreach ($primeros as $item) {
            $bien = $item['bien'];
            $cantidad = isset($item['cantidad']) ? $item['cantidad'] : 1;
            $this->pdf->SetXY(30, $y);
            $this->pdf->Write($lineHeight, $cantidad);

            $naturaleza = $bien->getNaturaleza();
            $this->pdf->SetXY(70, $y);
            $this->pdf->Write($lineHeight, $naturaleza);

            $descripcion = $bien->getDescripcion();
            $this->pdf->SetXY(100, $y);
            $this->pdf->Write($lineHeight, $descripcion);
            
            $y += $lineHeight;
        }
    }

    private function generarAnexo($trabajador, $bienes, $datosAdicionales) {
        $this->pdf->AddPage();
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetAutoPageBreak(true, 50);

        // ENCABEZADO
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 10, "ANEXO - CONSTANCIA DE SALIDA DE BIENES", 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, "FOLIO: " . $datosAdicionales['folio'], 0, 1, 'C');
        $this->pdf->Ln(8);

        // Información de estado global
        $estadoGlobal = isset($datosAdicionales['estado_general']) ? $datosAdicionales['estado_general'] : 'Buenas condiciones';
        $sujetoDevolucionGlobal = isset($datosAdicionales['sujeto_devolucion_global']) && $datosAdicionales['sujeto_devolucion_global'] == 1 ? 'SÍ' : 'NO';
        
        $this->pdf->SetFont('helvetica', '', 8);
        $infoExtra = "Estado General: " . $estadoGlobal . " | Sujeto a Devolución: " . $sujetoDevolucionGlobal;
        
        // Agregar fecha de devolución si aplica
        if ($sujetoDevolucionGlobal === 'SÍ' && isset($datosAdicionales['fecha_devolucion_constancia']) && !empty($datosAdicionales['fecha_devolucion_constancia'])) {
            $fechaDevolucionFormateada = $this->generarTextoFechaCorta($datosAdicionales['fecha_devolucion_constancia']);
            $infoExtra .= " | Fecha de Devolución: " . $fechaDevolucionFormateada;
        }
        
        $this->pdf->Cell(0, 5, $infoExtra, 0, 1, 'L');
        $this->pdf->Ln(3);

        // TABLA DE BIENES
        $this->pdf->SetFillColor(235, 235, 235);
        $this->pdf->SetFont('helvetica', '', 8);
        
        $w = array(15, 25, 45, 30, 30, 35);
        
        $this->pdf->Cell($w[0], 7, 'CANT.', 1, 0, 'C', 1);
        $this->pdf->Cell($w[1], 7, 'NAT.', 1, 0, 'C', 1);
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
            if($h < 7) $h = 7;

            $this->pdf->MultiCell($w[0], $h, $cant, 1, 'C', 0, 0);
            $this->pdf->MultiCell($w[1], $h, $bien->getNaturaleza(), 1, 'C', 0, 0);
            $this->pdf->MultiCell($w[2], $h, $bien->getDescripcion(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[3], $h, $bien->getMarca(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[4], $h, $bien->getModelo(), 1, 'L', 0, 0);
            $this->pdf->MultiCell($w[5], $h, $bien->getSerie(), 1, 'L', 0, 1);
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
        $this->pdf->Cell(80, 4, "RECIBE", 0, 1, 'C');
        
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
        $nombreEntrega = isset($datosAdicionales['entrega_resguardo']) ? $datosAdicionales['entrega_resguardo'] : $trabajador->getNombre();
        $this->pdf->Cell(80, 4, $nombreEntrega, 0, 1, 'C');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', '', 6.5);
        $cargoEntrega = isset($datosAdicionales['cargo_entrega']) ? $datosAdicionales['cargo_entrega'] : "COORDINACIÓN DE INFORMÁTICA";
        $this->pdf->MultiCell(80, 3, $cargoEntrega, 0, 'C');
    }
}