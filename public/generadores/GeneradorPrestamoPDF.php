<?php
// generadores/GeneradorPrestamoPDF.php

// Cargar autoload
require_once __DIR__ . '/../../vendor/autoload.php';

// Importar clase FPDI para TCPDF
use setasign\Fpdi\Tcpdf\Fpdi;

class GeneradorPrestamoPDF
{
    private $pdf;
    private $plantilla;
    
    public function __construct()
    {
        // Buscar plantilla en varios lugares
        $posiblesRutas = [
            __DIR__ . '/../../plantillas/prestamo1.pdf',
            __DIR__ . '/../plantillas/prestamo1.pdf',
            '/mnt/user-data/uploads/prestamo1.pdf'
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
            // Crear instancia de FPDI (extiende TCPDF)
            $this->pdf = new Fpdi();
            
            // Configuración del documento
            $this->pdf->SetCreator('Sistema IMSS');
            $this->pdf->SetAuthor('IMSS Control de Bienes');
            $this->pdf->SetTitle('Constancia de Préstamo');
            $this->pdf->SetSubject('Forma CBM-9');
            
            $this->pdf->SetMargins(15, 15, 15);
            $this->pdf->SetAutoPageBreak(true, 20);
            
            // Si existe plantilla, importarla
            if ($this->plantilla && file_exists($this->plantilla)) {
                try {
                    $pageCount = $this->pdf->setSourceFile($this->plantilla);
                    $templateId = $this->pdf->importPage(1);
                    $this->pdf->AddPage();
                    $this->pdf->useTemplate($templateId, 0, 0);
                } catch (Exception $e) {
                    // Si falla, crear página sin plantilla
                    $this->pdf->AddPage();
                    $this->agregarEncabezado();
                }
            } else {
                // Crear página sin plantilla
                $this->pdf->AddPage();
                $this->agregarEncabezado();
            }
            
            // Llenar datos
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->SetTextColor(0, 0, 0);
            
            $this->llenarDatosTrabajador($trabajador);
            $this->llenarBienes($bienes);
            $this->llenarDatosAdicionales($datosAdicionales);
            $this->llenarFirmas($datosAdicionales);
            
            // Guardar PDF
            $this->pdf->Output($rutaSalida, 'F');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error generando PDF de préstamo: " . $e->getMessage());
            throw new Exception("Error al generar PDF: " . $e->getMessage());
        }
    }
    
    private function agregarEncabezado()
    {
        // Logo IMSS (si existe)
        $logoPath = __DIR__ . '/../../public/images/logo_imss.png';
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, 15, 10, 25);
        }
        
        // Título principal
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetY(15);
        $this->pdf->Cell(0, 10, 'INSTITUTO MEXICANO DEL SEGURO SOCIAL', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 8, 'CONSTANCIA DE PRÉSTAMO DE BIENES', 0, 1, 'C');
        
        // Línea separadora
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY() + 2, $this->pdf->getPageWidth() - 15, $this->pdf->GetY() + 2);
        $this->pdf->Ln(8);
        
        // Texto introductorio
        $this->pdf->SetFont('helvetica', '', 9);
        $texto = 'Recibí del responsable del control administrativo de bienes en calidad de préstamo, ' .
                 'los bienes que se detallan a continuación, comprometiéndome a devolverlos en un plazo ' .
                 'que por ningún motivo excederá los 30 días calendario.';
        $this->pdf->MultiCell(0, 5, $texto, 0, 'J');
        $this->pdf->Ln(5);
    }
    
    private function llenarDatosTrabajador($trabajador)
    {
        // Sección de datos del trabajador
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'DATOS DEL TRABAJADOR', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        // Tabla de datos
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        
        // Nombre
        $this->pdf->Cell(40, 6, 'Nombre:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getNombre(), 1, 1, 'L');
        
        // Cargo
        $this->pdf->Cell(40, 6, 'Cargo:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getCargo() ?: 'N/A', 1, 1, 'L');
        
        // Adscripción
        $this->pdf->Cell(40, 6, 'Adscripción:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getAdscripcion() ?: 'N/A', 1, 1, 'L');
        
        // Matrícula
        $this->pdf->Cell(40, 6, 'Matrícula:', 1, 0, 'L', true);
        $this->pdf->Cell(140, 6, $trabajador->getMatricula(), 1, 1, 'L');
        
        $this->pdf->Ln(5);
    }
    
    private function llenarBienes($bienes)
    {
        // Encabezado de tabla de bienes
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'BIENES EN PRÉSTAMO', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        // Encabezados de columnas
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        
        $this->pdf->Cell(15, 7, 'CANT.', 1, 0, 'C', true);
        $this->pdf->Cell(20, 7, 'NAT.', 1, 0, 'C', true);
        $this->pdf->Cell(40, 7, 'IDENTIFICACIÓN', 1, 0, 'C', true);
        $this->pdf->Cell(105, 7, 'DESCRIPCIÓN', 1, 1, 'C', true);
        
        // Datos de bienes
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255, 255, 255);
        
        foreach ($bienes as $bienData) {
            $bien = $bienData['bien'];
            $cantidad = $bienData['cantidad'];
            
            // Verificar si necesitamos nueva página
            if ($this->pdf->GetY() > 250) {
                $this->pdf->AddPage();
                $this->pdf->SetY(30);
            }
            
            $this->pdf->Cell(15, 6, $cantidad, 1, 0, 'C');
            $this->pdf->Cell(20, 6, $bien->getNaturaleza(), 1, 0, 'C');
            $this->pdf->Cell(40, 6, $bien->getIdentificacion() ?: 'N/A', 1, 0, 'L');
            
            // Descripción (truncar si es muy larga)
            $descripcion = $bien->getDescripcion();
            if (strlen($descripcion) > 60) {
                $descripcion = substr($descripcion, 0, 57) . '...';
            }
            $this->pdf->Cell(105, 6, $descripcion, 1, 1, 'L');
        }
        
        $this->pdf->Ln(5);
    }
    
    private function llenarDatosAdicionales($datos)
    {
        // Nota adicional
        if (!empty($datos['nota'])) {
            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->Cell(20, 5, 'NOTA:', 0, 0, 'L');
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->MultiCell(0, 5, $datos['nota'], 0, 'J');
            $this->pdf->Ln(3);
        }
        
        // Estado físico
        $this->pdf->SetFont('helvetica', '', 9);
        $texto = 'Durante el tiempo que los bienes permanezcan en mi poder, asumo la responsabilidad de los mismos.';
        $this->pdf->MultiCell(0, 5, $texto, 0, 'J');
        $this->pdf->Ln(5);
    }
    
    private function llenarFirmas($datos)
    {
        // Posición Y para las firmas
        $yFirmas = max($this->pdf->GetY() + 10, 230);
        
        if ($yFirmas > 250) {
            $this->pdf->AddPage();
            $yFirmas = 50;
        }
        
        $this->pdf->SetY($yFirmas);
        
        // Columna izquierda - AUTORIZA
        $xIzq = 25;
        $this->pdf->SetXY($xIzq, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(80, 6, 'AUTORIZA', 0, 1, 'C');
        
        $this->pdf->SetX($xIzq);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(80, 5, 'Responsable del Control Administrativo', 0, 1, 'C');
        
        $this->pdf->Ln(10);
        $this->pdf->SetX($xIzq);
        $this->pdf->Cell(80, 0, '', 'T', 1, 'C');
        
        $this->pdf->SetX($xIzq);
        $this->pdf->Cell(80, 5, 'Nombre y Firma', 0, 1, 'C');
        
        $this->pdf->SetX($xIzq);
        $this->pdf->Cell(30, 5, 'Matrícula:', 0, 0, 'R');
        $valor = isset($datos['matricula_autoriza']) && $datos['matricula_autoriza'] !== ''
            ? $datos['matricula_autoriza']
            : '_______________';

        $this->pdf->Cell(50, 5, $valor, 0, 1, 'L');

        
        // Columna derecha - RECIBE
        $xDer = 115;
        $this->pdf->SetXY($xDer, $yFirmas);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(80, 6, 'RECIBE', 0, 1, 'C');
        
        $this->pdf->SetX($xDer);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(80, 5, 'Responsable del Bien', 0, 1, 'C');
        
        $this->pdf->SetXY($xDer, $yFirmas + 21);
        $this->pdf->Cell(80, 0, '', 'T', 1, 'C');
        
        $this->pdf->SetX($xDer);
        $this->pdf->Cell(80, 5, 'Nombre y Firma', 0, 1, 'C');
        
        $this->pdf->SetX($xDer);
        $this->pdf->Cell(30, 5, 'Matrícula:', 0, 0, 'R');
        $valor = isset($datos['matricula_recibe']) && $datos['matricula_recibe'] !== ''
            ? $datos['matricula_recibe']
            : '_______________';

        $this->pdf->Cell(50, 5, $valor, 0, 1, 'L');

        
        // Lugar y fecha
        $this->pdf->Ln(5);
        $this->pdf->SetFont('helvetica', '', 9);
        $lugarFecha = !empty($datos['lugar_fecha']) ? $datos['lugar_fecha'] : date('d/m/Y');
        $this->pdf->Cell(0, 5, 'Lugar y Fecha: ' . $lugarFecha, 0, 1, 'C');
        
        // Número de forma al pie
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(0, 10, 'Forma CBM-9', 0, 0, 'R');
    }
}