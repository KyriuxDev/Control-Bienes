<?php
// src/Infrastructure/Repository/MySQLMovimientoRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Domain\Entity\Movimiento;
use PDO;

class MySQLMovimientoRepository implements MovimientoRepositoryInterface
{
    protected $pdo;
    protected $table = 'movimiento';
    protected $entityClass = Movimiento::class;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function persist($entity)
    {
        if ($entity->getIdMovimiento()) {
            return $this->actualizar($entity);
        } else {
            return $this->guardar($entity);
        }
    }

    protected function guardar($entity)
    {
        $sql = "INSERT INTO {$this->table} (tipo_movimiento, matricula_recibe, matricula_entrega, fecha, lugar, area, folio, dias_prestamo, fecha_devolucion) 
                VALUES (:tipo_movimiento, :matricula_recibe, :matricula_entrega, :fecha, :lugar, :area, :folio, :dias_prestamo, :fecha_devolucion)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'tipo_movimiento' => $entity->getTipoMovimiento(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'matricula_entrega' => $entity->getMatriculaEntrega(),
            'fecha' => $entity->getFecha(),
            'lugar' => $entity->getLugar(),
            'area' => $entity->getArea(),
            'folio' => $entity->getFolio(),
            'dias_prestamo' => $entity->getDiasPrestamo(),
            'fecha_devolucion' => $entity->getFechaDevolucion() // NUEVO
        ]);
        
        if ($result) {
            $entity->setIdMovimiento($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function actualizar($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET tipo_movimiento = :tipo_movimiento,
                    matricula_recibe = :matricula_recibe,
                    matricula_entrega = :matricula_entrega, 
                    fecha = :fecha, 
                    lugar = :lugar, 
                    area = :area, 
                    folio = :folio,
                    dias_prestamo = :dias_prestamo,
                    fecha_devolucion = :fecha_devolucion
                WHERE id_movimiento = :id_movimiento";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_movimiento' => $entity->getIdMovimiento(),
            'tipo_movimiento' => $entity->getTipoMovimiento(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'matricula_entrega' => $entity->getMatriculaEntrega(),
            'fecha' => $entity->getFecha(),
            'lugar' => $entity->getLugar(),
            'area' => $entity->getArea(),
            'folio' => $entity->getFolio(),
            'dias_prestamo' => $entity->getDiasPrestamo(),
            'fecha_devolucion' => $entity->getFechaDevolucion() // NUEVO
        ]);
    }

    public function obtenerPorId($id_movimiento)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_movimiento = :id_movimiento");
        $stmt->execute(['id_movimiento' => $id_movimiento]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id_movimiento = :id_movimiento");
        return $stmt->execute(['id_movimiento' => $id]);
    }
}