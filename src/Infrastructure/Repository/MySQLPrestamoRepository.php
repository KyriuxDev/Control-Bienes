<?php
// src/Domain/Repository/MySQLPrestamoRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Prestamo;
use PDO;

class MySQLPrestamoRepository extends MySQLAbstractRepository implements PrestamoRepositoryInterface
{
    protected $table = 'prestamo';
    protected $entityClass = Prestamo::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (folio, trabajador_id, fecha_emision, fecha_devolucion_programada, fecha_devolucion_real, lugar, matricula_autoriza, matricula_recibe, estado, observaciones) 
                VALUES (:folio, :trabajador_id, :fecha_emision, :fecha_devolucion_programada, :fecha_devolucion_real, :lugar, :matricula_autoriza, :matricula_recibe, :estado, :observaciones)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'fecha_emision' => $entity->getFechaEmision(),
            'fecha_devolucion_programada' => $entity->getFechaDevolucionProgramada(),
            'fecha_devolucion_real' => $entity->getFechaDevolucionReal(),
            'lugar' => $entity->getLugar(),
            'matricula_autoriza' => $entity->getMatriculaAutoriza(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'estado' => $entity->getEstado(),
            'observaciones' => $entity->getObservaciones()
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
                    fecha_emision = :fecha_emision, 
                    fecha_devolucion_programada = :fecha_devolucion_programada, 
                    fecha_devolucion_real = :fecha_devolucion_real, 
                    lugar = :lugar, 
                    matricula_autoriza = :matricula_autoriza, 
                    matricula_recibe = :matricula_recibe, 
                    estado = :estado, 
                    observaciones = :observaciones
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'fecha_emision' => $entity->getFechaEmision(),
            'fecha_devolucion_programada' => $entity->getFechaDevolucionProgramada(),
            'fecha_devolucion_real' => $entity->getFechaDevolucionReal(),
            'lugar' => $entity->getLugar(),
            'matricula_autoriza' => $entity->getMatriculaAutoriza(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'estado' => $entity->getEstado(),
            'observaciones' => $entity->getObservaciones()
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

    public function findByFolio($folio)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE folio = :folio");
        $stmt->execute(['folio' => $folio]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    public function findVencidos()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE estado = 'ACTIVO' 
                AND fecha_devolucion_programada < CURDATE()";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }
}