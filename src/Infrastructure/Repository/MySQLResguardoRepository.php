<?php
// src/Domain/Repository/MySQLResguardoRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Resguardo;
use PDO;

class MySQLResguardoRepository extends MySQLAbstractRepository implements ResguardoRepositoryInterface
{
    protected $table = 'resguardo';
    protected $entityClass = Resguardo::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (folio, trabajador_id, bien_id, fecha_asignacion, fecha_devolucion, lugar, estado, notas_adicionales) 
                VALUES (:folio, :trabajador_id, :bien_id, :fecha_asignacion, :fecha_devolucion, :lugar, :estado, :notas_adicionales)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'bien_id' => $entity->getBienId(),
            'fecha_asignacion' => $entity->getFechaAsignacion(),
            'fecha_devolucion' => $entity->getFechaDevolucion(),
            'lugar' => $entity->getLugar(),
            'estado' => $entity->getEstado(),
            'notas_adicionales' => $entity->getNotasAdicionales()
        ]);
        
        if ($result) {
            $entity->setId($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function update($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET folio = :folio, 
                    trabajador_id = :trabajador_id, 
                    bien_id = :bien_id, 
                    fecha_asignacion = :fecha_asignacion, 
                    fecha_devolucion = :fecha_devolucion, 
                    lugar = :lugar, 
                    estado = :estado, 
                    notas_adicionales = :notas_adicionales
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'bien_id' => $entity->getBienId(),
            'fecha_asignacion' => $entity->getFechaAsignacion(),
            'fecha_devolucion' => $entity->getFechaDevolucion(),
            'lugar' => $entity->getLugar(),
            'estado' => $entity->getEstado(),
            'notas_adicionales' => $entity->getNotasAdicionales()
        ]);
    }

    public function findByEstado($estado)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findBytrabajador($trabajador_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE trabajador_id = :trabajador_id");
        $stmt->execute(['trabajador_id' => $trabajador_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findByBien($bien_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE bien_id = :bien_id");
        $stmt->execute(['bien_id' => $bien_id]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findByFolio($folio)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE folio = :folio");
        $stmt->execute(['folio' => $folio]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    public function findActivos()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE estado = 'ACTIVO'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }
}