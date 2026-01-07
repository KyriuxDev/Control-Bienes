<?php
// src/Infrastructure/Helper/FolioGenerator.php
namespace App\Infrastructure\Helper;

use PDO;

class FolioGenerator
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Genera un folio único en formato: YEAR/NÚMERO
     * Ejemplo: 2026/001, 2026/002, etc.
     */
    public function generarFolio()
    {
        $anio = date('Y');
        
        // Buscar el último folio del año actual
        $stmt = $this->pdo->prepare("
            SELECT folio 
            FROM movimiento 
            WHERE folio LIKE :patron 
            AND folio NOT LIKE :patronConGuion
            ORDER BY CAST(SUBSTRING_INDEX(folio, '/', -1) AS UNSIGNED) DESC
            LIMIT 1
        ");
        $stmt->execute([
            'patron' => $anio . '/%',
            'patronConGuion' => $anio . '/%-' // Excluir folios temporales con timestamp
        ]);
        $ultimoFolio = $stmt->fetchColumn();
        
        if ($ultimoFolio) {
            // Extraer el número del folio
            $partes = explode('/', $ultimoFolio);
            if (isset($partes[1])) {
                // Extraer solo el número (por si hay algo después del número)
                $numeroStr = preg_replace('/[^0-9]/', '', $partes[1]);
                $numero = intval($numeroStr);
                $nuevoNumero = $numero + 1;
            } else {
                $nuevoNumero = 1;
            }
        } else {
            // Es el primer folio del año
            $nuevoNumero = 1;
        }
        
        // Formatear con ceros a la izquierda (3 dígitos)
        return $anio . '/' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verifica si un folio existe en la base de datos
     */
    public function folioExiste($folio)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM movimiento WHERE folio = :folio");
        $stmt->execute(['folio' => $folio]);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Genera un folio único garantizado (con bloqueo de tabla para evitar conflictos)
     */
    public function generarFolioUnico()
    {
        try {
            // Iniciar transacción para bloquear la tabla
            $this->pdo->beginTransaction();
            
            // Bloquear tabla para evitar condiciones de carrera
            $this->pdo->exec("LOCK TABLES movimiento WRITE");
            
            // Generar folio
            $folio = $this->generarFolio();
            
            // Verificar que no exista (por seguridad)
            $contador = 0;
            while ($this->folioExiste($folio) && $contador < 100) {
                // Si existe, incrementar manualmente
                $partes = explode('/', $folio);
                $numero = intval(preg_replace('/[^0-9]/', '', $partes[1]));
                $numero++;
                $folio = date('Y') . '/' . str_pad($numero, 3, '0', STR_PAD_LEFT);
                $contador++;
            }
            
            // Desbloquear tabla
            $this->pdo->exec("UNLOCK TABLES");
            
            // Hacer commit
            $this->pdo->commit();
            
            return $folio;
            
        } catch (\Exception $e) {
            // En caso de error, desbloquear y rollback
            try {
                $this->pdo->exec("UNLOCK TABLES");
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
            } catch (\Exception $e2) {
                // Ignorar errores de unlock/rollback
            }
            
            // Devolver folio simple sin bloqueo
            error_log("Error al generar folio con bloqueo: " . $e->getMessage());
            return $this->generarFolio();
        }
    }
}