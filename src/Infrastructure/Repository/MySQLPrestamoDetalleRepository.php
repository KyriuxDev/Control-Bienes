<?php
// src/Domain/Repository/MySQLPrestamoDetalleRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\PrestamoDetalle;
use PDO;

class MySQLPrestamoDetalleRepository extends MySQLAbstractRepository implements PrestamoDetalleRepositoryInterface
{
    protected $table = 'prestamo_detalle';
    protected $entityClass = PrestamoDetalle::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (prestamo_id, bien_id, cantidad) 
                VALUES (:prestamo_id, :bien_id, :cantidad)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'prestamo_id' => $entity->getPrestamoId(),
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
                SET prestamo_id = :prestamo_id, 
                    bien_id = :bien_id, 
                    cantidad = :cantidad
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'prestamo_id' => $entity->getPrestamoId(),
            'bien_id' => $entity->getBienId(),
            'cantidad' => $entity->getCantidad()
        ]);
    }

    public function findByPrestamo($prestamo_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE prestamo_id = :prestamo_id");
        $stmt->execute(['prestamo_id' => $prestamo_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findByBien($bien_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE bien_id = :bien_id");
        $stmt->execute(['bien_id' => $bien_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function deleteByPrestamo($prestamo_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE prestamo_id = :prestamo_id");
        return $stmt->execute(['prestamo_id' => $prestamo_id]);
    }
}