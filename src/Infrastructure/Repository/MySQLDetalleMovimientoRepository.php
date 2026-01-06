<?php
// src/Infrastructure/Repository/MySQLDetalleMovimientoRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Domain\Entity\DetalleMovimiento;
use PDO;

class MySQLDetalleMovimientoRepository implements DetalleMovimientoRepositoryInterface
{
    protected $pdo;
    protected $table = 'detalle_movimiento';
    protected $entityClass = Detalle_Movimiento::class;

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
        $sql = "INSERT INTO {$this->table} (tipo_movimiento, matricula_recibe, matricula_entrega, fecha, lugar, area, folio, dias_prestamo) 
                VALUES (:tipo_movimiento, :matricula_recibe, :matricula_entrega, :fecha, :lugar, :area, :folio, :dias_prestamo)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'tipo_movimiento' => $entity->getTipoMovimiento(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'matricula_entrega' => $entity->getMatriculaEntrega(),
            'fecha' => $entity->getFecha(),
            'lugar' => $entity->getLugar(),
            'area' => $entity->getArea(),
            'folio' => $entity->getFolio(),
            'dias_prestamo' => $entity->getDiasPrestamo()
        ]);
        
        if ($result) {
            $entity->setIdBien($this->pdo->lastInsertId());
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
                    'dias_prestamo = :dias_prestamo,
                WHERE id_movimiento = :id_movimiento";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_movimiento' => $entity->getIdMovimiento(),
            'matricula_recibe' => $entity->getMatriculaRecibe(),
            'matricula_entrega' => $entity->getMatriculaEntrega(),
            'fecha' => $entity->getFecha(),
            'lugar' => $entity->getLugar(),
            'area' => $entity->getArea(),
            'folio' => $entity->getFolio(),
            'dias_prestamo' => $entity->getDiasPrestamo()
        ]);
    }

    public function obtenerPorId($id_bien)
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
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id_bien = :id_bien");
        return $stmt->execute(['id_movimiento' => $id]);
    }

}