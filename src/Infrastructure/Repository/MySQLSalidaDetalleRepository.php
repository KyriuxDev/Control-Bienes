<?php
// src/Domain/Repository/MySQLSalidaDetalleRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\SalidaDetalle;
use PDO;

class MySQLSalidaDetalleRepository extends MySQLAbstractRepository implements SalidaDetalleRepositoryInterface
{
    protected $table = 'salida_detalle';
    protected $entityClass = SalidaDetalle::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (salida_id, bien_id, cantidad) 
                VALUES (:salida_id, :bien_id, :cantidad)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'salida_id' => $entity->getSalidaId(),
            'bien_id' => $entity->getBienId(),
            'cantidad' => $entity->getCantidad()
        ]);
        
        if ($result) {
            $entity->setId($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function update($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET salida_id = :salida_id, 
                    bien_id = :bien_id, 
                    cantidad = :cantidad
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'salida_id' => $entity->getSalidaId(),
            'bien_id' => $entity->getBienId(),
            'cantidad' => $entity->getCantidad()
        ]);
    }

    public function findBySalida($salida_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE salida_id = :salida_id");
        $stmt->execute(['salida_id' => $salida_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findByBien($bien_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE bien_id = :bien_id");
        $stmt->execute(['bien_id' => $bien_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function deleteBySalida($salida_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE salida_id = :salida_id");
        return $stmt->execute(['salida_id' => $salida_id]);
    }
}