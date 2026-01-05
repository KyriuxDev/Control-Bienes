<?php
// generadores/GeneradorResguardoPDF.php

require_once __DIR__ . '/../../vendor/autoload.php';
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorResguardoPDF
{
    private $pdf;
    private $plantilla;
    
    public function __construct()
    {
        $posiblesRutas = [
            __DIR__ . '/../../templates/resguardo.pdf' ];
        
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
            $this->pdf->SetTitle('Resguardo de Bienes');
            $this->pdf->SetSubject('Forma CMB-3');
            
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
            $this->llenarDatosUsuario($trabajador);
            $this->llenarBienes($bienes);
            $this->llenarCondiciones();
            $this->llenarFirmas($datosAdicionales);
            
            $this->pdf->Output($rutaSalida, 'F');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error generando PDF de resguardo: " . $e->getMessage());
            throw new Exception("Error al generar PDF: " . $e->getMessage());
        }
    }
    
    private function agregarEncabezado()
    {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetY(15);
        $this->pdf->Cell(0, 10, 'INSTITUTO MEXICANO DEL SEGURO SOCIAL', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 8, 'RESGUARDO DE BIENES MUEBLES', 0, 1, 'C');
        
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY() + 2, $this->pdf->getPageWidth() - 15, $this->pdf->GetY() + 2);
        $this->pdf->Ln(8);
    }
    
    private function llenarDatosGenerales($datos)
    {
        $this->pdf->SetFont('helvetica', '', 9);
        
        // Folio y Fecha
        $this->pdf->Cell(40, 6, 'Folio:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 9);
        $folio = isset($datos['folio']) && $datos['folio'] !== ''
            ? $datos['folio']
            : 'N/A';
        $this->pdf->Cell(60, 6, $folio, 0, 0, 'L');
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(30, 6, 'Fecha:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 9);
        $lugarFecha = isset($datos['lugar_fecha']) && $datos['lugar_fecha'] !== ''
            ? $datos['lugar_fecha']
            : date('d/m/Y');
        $this->pdf->Cell(0, 6, $lugarFecha, 0, 1, 'L');
        
        $this->pdf->Ln(5);
    }
    
    private function llenarDatosUsuario($trabajador)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'DATOS DEL USUARIO', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        
        $this->pdf->Cell(40, 6, 'Nombre Completo:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getNombre(), 1, 1, 'L');
        
        $this->pdf->Cell(40, 6, 'Cargo:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getCargo() ?: 'N/A', 1, 1, 'L');
        
        $this->pdf->Cell(40, 6, 'Adscripción:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getAdscripcion() ?: 'N/A', 1, 1, 'L');
        
        $this->pdf->Cell(40, 6, 'Teléfono:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getTelefono() ?: 'N/A', 1, 1, 'L');
        
        $this->pdf->Ln(5);
    }
    
    private function llenarBienes($bienes)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'BIENES EN RESGUARDO', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        
        $this->pdf->Cell(15, 7, 'CANT.', 1, 0, 'C', true);
        $this->pdf->Cell(45, 7, 'DESCRIPCIÓN', 1, 0, 'C', true);
        $this->pdf->Cell(35, 7, 'MARCA/MODELO', 1, 0, 'C', true);
        $this->pdf->Cell(40, 7, 'NO. SERIE', 1, 0, 'C', true);
        $this->pdf->Cell(45, 7, 'OBSERVACIONES', 1, 1, 'C', true);
        
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255, 255, 255);
        
        foreach ($bienes as $bienData) {
            $bien = $bienData['bien'];
            $cantidad = $bienData['cantidad'];
            
            if ($this->pdf->GetY() > 240) {
                $this->pdf->AddPage();
                $this->pdf->SetY(30);
            }
            
            $this->pdf->Cell(15, 6, $cantidad, 1, 0, 'C');
            
            $descripcion = $bien->getDescripcion();
            if (strlen($descripcion) > 25) {
                $descripcion = substr($descripcion, 0, 22) . '...';
            }
            $this->pdf->Cell(45, 6, $descripcion, 1, 0, 'L');
            
            $marca = $bien->getMarca() ?: 'N/A';
            $modelo = $bien->getModelo();
            $marcaModelo = $marca . ($modelo ? ' ' . $modelo : '');
            if (strlen($marcaModelo) > 20) {
                $marcaModelo = substr($marcaModelo, 0, 17) . '...';
            }
            $this->pdf->Cell(35, 6, $marcaModelo, 1, 0, 'L');
            
            // CORREGIDO: Usar getSerie() en lugar de getNumeroSerie()
            $serie = $bien->getSerie() ?: 'S/N';
            if (strlen($serie) > 20) {
                $serie = substr($serie, 0, 17) . '...';
            }
            $this->pdf->Cell(40, 6, $serie, 1, 0, 'L');
            
            $this->pdf->Cell(45, 6, '', 1, 1, 'L');
        }
        
        $this->pdf->Ln(5);
    }
    
    private function llenarCondiciones()
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'CONDICIONES GENERALES DE USO', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('helvetica', '', 8);
        
        $condiciones = [
            'El usuario se compromete a usar los bienes exclusivamente para actividades institucionales.',
            'Los bienes deben ser utilizados con cuidado y responsabilidad.',
            'Cualquier daño, pérdida o robo debe ser reportado inmediatamente.',
            'No está permitido prestar los bienes a terceros sin autorización.',
            'El usuario es responsable del mantenimiento básico y limpieza.',
            'Los bienes no pueden ser modificados sin autorización expresa.',
            'Al término de la relación laboral, los bienes deben ser devueltos.',
            'El incumplimiento de estas condiciones puede resultar en sanciones.',
            'Se acepta la verificación física periódica por parte del área responsable.'
        ];
        
        foreach ($condiciones as $i => $condicion) {
            $this->pdf->Cell(10, 5, ($i + 1) . '.', 0, 0, 'L');
            $this->pdf->MultiCell(0, 5, $condicion, 0, 'J');
        }
        
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
        
        // ENTREGA
        $xIzq = 25;
        $this->pdf->SetXY($xIzq, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(80, 6, 'ENTREGA', 0, 1, 'C');
        
        $this->pdf->SetX($xIzq);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(80, 5, 'Responsable de Bienes', 0, 1, 'C');
        
        $this->pdf->Ln(10);
        $this->pdf->SetX($xIzq);
        $this->pdf->Cell(80, 0, '', 'T', 1, 'C');
        
        $this->pdf->SetX($xIzq);
        $this->pdf->Cell(80, 5, 'Nombre y Firma', 0, 1, 'C');
        
        // RECIBE
        $xDer = 115;
        $this->pdf->SetXY($xDer, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(80, 6, 'RECIBE', 0, 1, 'C');
        
        $this->pdf->SetX($xDer);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(80, 5, 'Usuario Resguardante', 0, 1, 'C');
        
        $this->pdf->SetXY($xDer, $yFirmas + 21);
        $this->pdf->Cell(80, 0, '', 'T', 1, 'C');
        
        $this->pdf->SetX($xDer);
        $this->pdf->Cell(80, 5, 'Nombre y Firma', 0, 1, 'C');
        
        // Número de forma
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(0, 10, 'Forma CMB-3', 0, 0, 'R');
    }
}