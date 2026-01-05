<?php
// generadores/GeneradorSalidaPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorSalidaPDF
{
    private $pdf;
    private $plantilla;
    
    public function __construct()
    {
        $posiblesRutas = [
            __DIR__ . '/../../plantillas/salidaBiene.pdf',
            __DIR__ . '/../plantillas/salidaBiene.pdf',
            '/mnt/user-data/uploads/salidaBiene.pdf'
        ];
        
        foreach ($posiblesRutas as $ruta) {
            if (file_exists($ruta)) {
                $this->plantilla = $ruta;
                break;
            }
        }
    }
    
    public function generar($trabajador, $bienes, $datosAdicionales, $rutaSalida)
    {
        try {
            $this->pdf = new Fpdi();
            
            $this->pdf->SetCreator('Sistema IMSS');
            $this->pdf->SetAuthor('IMSS Control de Bienes');
            $this->pdf->SetTitle('Autorización de Salida de Bienes');
            $this->pdf->SetSubject('Forma CBM-2');
            
            $this->pdf->SetMargins(15, 15, 15);
            $this->pdf->SetAutoPageBreak(true, 20);
            
            if ($this->plantilla && file_exists($this->plantilla)) {
                try {
                    $pageCount = $this->pdf->setSourceFile($this->plantilla);
                    $templateId = $this->pdf->importPage(1);
                    $this->pdf->AddPage();
                    $this->pdf->useTemplate($templateId, 0, 0);
                } catch (Exception $e) {
                    $this->pdf->AddPage();
                    $this->agregarEncabezado();
                }
            } else {
                $this->pdf->AddPage();
                $this->agregarEncabezado();
            }
            
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->SetTextColor(0, 0, 0);
            
            $this->llenarDatosGenerales($datosAdicionales);
            $this->llenarDatosTrabajador($trabajador);
            $this->llenarBienes($bienes);
            $this->llenarObservaciones($datosAdicionales);
            $this->agregarLeyenda();
            $this->llenarFirmas($datosAdicionales);
            
            $this->pdf->Output($rutaSalida, 'F');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error generando PDF de salida: " . $e->getMessage());
            throw new Exception("Error al generar PDF: " . $e->getMessage());
        }
    }
    
    private function agregarEncabezado()
    {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetY(15);
        $this->pdf->Cell(0, 10, 'INSTITUTO MEXICANO DEL SEGURO SOCIAL', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 8, 'AUTORIZACIÓN DE SALIDA DE BIENES', 0, 1, 'C');
        
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY() + 2, $this->pdf->getPageWidth() - 15, $this->pdf->GetY() + 2);
        $this->pdf->Ln(8);
    }
    
    private function llenarDatosGenerales($datos)
    {
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        
        // Área de origen
        $areaOrigen = isset($datos['area_origen']) && $datos['area_origen'] !== ''
            ? $datos['area_origen']
            : 'N/A';

        $this->pdf->Cell(45, 6, 'Área de Origen:', 1, 0, 'L', true);
        $this->pdf->Cell(135, 6, $areaOrigen, 1, 1, 'L');

        // Destino
        $destino = isset($datos['destino']) && $datos['destino'] !== ''
            ? $datos['destino']
            : 'N/A';

        $this->pdf->Cell(45, 6, 'Destino:', 1, 0, 'L', true);
        $this->pdf->Cell(135, 6, $destino, 1, 1, 'L');

        // Motivo
        $motivo = isset($datos['motivo']) && $datos['motivo'] !== ''
            ? $datos['motivo']
            : 'N/A';

        $this->pdf->Cell(45, 6, 'Motivo:', 1, 0, 'L', true);
        $this->pdf->Cell(135, 6, $motivo, 1, 1, 'L');

        
        // Sujeto a devolución
        $this->pdf->Cell(45, 6, 'Sujeto a Devolución:', 1, 0, 'L', true);
        $sujetoDevolucion = !empty($datos['sujeto_devolucion']) && $datos['sujeto_devolucion'] === 'si' ? 'SÍ' : 'NO';
        $this->pdf->Cell(50, 6, $sujetoDevolucion, 1, 0, 'C');
        
        // Fecha de devolución
        $this->pdf->Cell(40, 6, 'Fecha Devolución:', 1, 0, 'L', true);
        $fechaDevolucion = !empty($datos['fecha_devolucion']) ? $datos['fecha_devolucion'] : 'N/A';
        $this->pdf->Cell(45, 6, $fechaDevolucion, 1, 1, 'C');
        
        $this->pdf->Ln(5);
    }
    
    private function llenarDatosTrabajador($trabajador)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'RESPONSABLE DE LA SALIDA', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        
        $this->pdf->Cell(40, 6, 'Nombre:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getNombre(), 1, 1, 'L');
        
        $this->pdf->Cell(40, 6, 'Cargo:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getCargo() ?: 'N/A', 1, 1, 'L');
        
        $this->pdf->Cell(40, 6, 'Matrícula:', 1, 0, 'L', true);
        $this->pdf->Cell(60, 6, $trabajador->getMatricula(), 1, 0, 'L');
        $this->pdf->Cell(40, 6, 'Teléfono:', 1, 0, 'L', true);
        $this->pdf->Cell(40, 6, $trabajador->getTelefono() ?: 'N/A', 1, 1, 'L');
        
        $this->pdf->Ln(5);
    }
    
    private function llenarBienes($bienes)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'RELACIÓN DE BIENES', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        
        $this->pdf->Cell(15, 7, 'CANT.', 1, 0, 'C', true);
        $this->pdf->Cell(20, 7, 'NAT.', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'IDENTIFICACIÓN', 1, 0, 'C', true);
        $this->pdf->Cell(80, 7, 'DESCRIPCIÓN', 1, 0, 'C', true);
        $this->pdf->Cell(30, 7, 'ESTADO', 1, 1, 'C', true);
        
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255, 255, 255);
        
        foreach ($bienes as $bienData) {
            $bien = $bienData['bien'];
            $cantidad = $bienData['cantidad'];
            
            if ($this->pdf->GetY() > 230) {
                $this->pdf->AddPage();
                $this->pdf->SetY(30);
            }
            
            $this->pdf->Cell(15, 6, $cantidad, 1, 0, 'C');
            $this->pdf->Cell(20, 6, $bien->getNaturaleza(), 1, 0, 'C');
            $this->pdf->Cell(35, 6, $bien->getIdentificacion() ?: 'N/A', 1, 0, 'L');
            
            $descripcion = $bien->getDescripcion();
            if (strlen($descripcion) > 45) {
                $descripcion = substr($descripcion, 0, 42) . '...';
            }
            $this->pdf->Cell(80, 6, $descripcion, 1, 0, 'L');
            
            $this->pdf->Cell(30, 6, 'Bueno', 1, 1, 'C');
        }
        
        $this->pdf->Ln(5);
    }
    
    private function llenarObservaciones($datos)
    {
        if (!empty($datos['observaciones'])) {
            $this->pdf->SetFont('helvetica', 'B', 10);
            $this->pdf->Cell(0, 6, 'OBSERVACIONES', 0, 1, 'L');
            
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->MultiCell(0, 5, $datos['observaciones'], 1, 'J');
            $this->pdf->Ln(3);
        }
    }
    
    private function agregarLeyenda()
    {
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 5, 'NATURALEZA DE LOS BIENES:', 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 8);
        $leyenda = 'BC = Bien Consumible | BMC = Bien Mueble Capitalizable | ' .
                   'BMNC = Bien Mueble No Capitalizable | BPS = Bien Propiedad del Solicitante';
        $this->pdf->MultiCell(0, 4, $leyenda, 0, 'L');
        $this->pdf->Ln(5);
    }
    
    private function llenarFirmas($datos)
    {
        $yFirmas = max($this->pdf->GetY() + 10, 240);
        
        if ($yFirmas > 260) {
            $this->pdf->AddPage();
            $yFirmas = 50;
        }
        
        $this->pdf->SetY($yFirmas);
        
        // Tres columnas de firmas
        $anchoCol = 60;
        $xCol1 = 15;
        $xCol2 = 80;
        $xCol3 = 145;
        
        // AUTORIZA
        $this->pdf->SetXY($xCol1, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell($anchoCol, 6, 'AUTORIZA', 0, 1, 'C');
        
        $this->pdf->SetX($xCol1);
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->Cell($anchoCol, 4, 'Jefe de Área', 0, 1, 'C');
        
        $this->pdf->SetXY($xCol1, $yFirmas + 20);
        $this->pdf->Cell($anchoCol, 0, '', 'T', 1, 'C');
        $this->pdf->SetX($xCol1);
        $this->pdf->Cell($anchoCol, 4, 'Nombre y Firma', 0, 1, 'C');
        
        // RESPONSABLE
        $this->pdf->SetXY($xCol2, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell($anchoCol, 6, 'RESPONSABLE', 0, 1, 'C');
        
        $this->pdf->SetX($xCol2);
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->Cell($anchoCol, 4, 'Encargado de Bienes', 0, 1, 'C');
        
        $this->pdf->SetXY($xCol2, $yFirmas + 20);
        $this->pdf->Cell($anchoCol, 0, '', 'T', 1, 'C');
        $this->pdf->SetX($xCol2);
        $this->pdf->Cell($anchoCol, 4, 'Nombre y Firma', 0, 1, 'C');
        
        // RECIBE
        $this->pdf->SetXY($xCol3, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell($anchoCol, 6, 'RECIBE', 0, 1, 'C');
        
        $this->pdf->SetX($xCol3);
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->Cell($anchoCol, 4, 'Usuario', 0, 1, 'C');
        
        $this->pdf->SetXY($xCol3, $yFirmas + 20);
        $this->pdf->Cell($anchoCol, 0, '', 'T', 1, 'C');
        $this->pdf->SetX($xCol3);
        $this->pdf->Cell($anchoCol, 4, 'Nombre y Firma', 0, 1, 'C');
        
        // Fecha
        $this->pdf->Ln(8);
        $this->pdf->SetFont('helvetica', '', 9);
        $fecha = !empty($datos['lugar_fecha']) ? $datos['lugar_fecha'] : date('d/m/Y');
        $this->pdf->Cell(0, 5, 'Fecha: ' . $fecha, 0, 1, 'C');
        
        // Número de forma
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(0, 10, 'Forma CBM-2', 0, 0, 'R');
    }
}