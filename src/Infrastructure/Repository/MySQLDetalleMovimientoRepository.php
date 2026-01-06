<?php
// src/Infrastructure/Repository/MySQLDetalleMovimientoRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Domain\Entity\Detalle_Movimiento;
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

        $existe = $this->buscarPorMovimientoYBien($entity->getIdMovimiento(), $entity->getIdBien());
        
        if ($existe) {
            return $this->actualizar($entity);
        } else {
            return $this->guardar($entity);
        }
    }

    protected function guardar($entity)
    {
        $sql = "INSERT INTO {$this->table} (id_movimiento, id_bien, cantidad, estado_fisico, sujeto_devolucion) 
                VALUES (:id_movimiento, :id_bien, :cantidad, :estado_fisico, :sujeto_devolucion)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_movimiento' => $entity->getIdMovimiento(),
            'id_bien' => $entity->getIdBien(),
            'cantidad' => $entity->getCantidad(),
            'estado_fisico' => $entity->getEstadoFisico(),
            'sujeto_devolucion' => $entity->getSujetoDevolucion()
        ]);
    }

    protected function actualizar($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET cantidad = :cantidad,
                    estado_fisico = :estado_fisico,
                    sujeto_devolucion = :sujeto_devolucion
                WHERE id_movimiento = :id_movimiento 
                AND id_bien = :id_bien";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_movimiento' => $entity->getIdMovimiento(),
            'id_bien' => $entity->getIdBien(),
            'cantidad' => $entity->getCantidad(),
            'estado_fisico' => $entity->getEstadoFisico(),
            'sujeto_devolucion' => $entity->getSujetoDevolucion()
        ]);
    }

    public function obtenerPorId($id)
    {
        // Como la clave es compuesta, este método podría no ser muy útil
        // Podrías considerar cambiar la interfaz o interpretarlo de otra manera
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_movimiento = :id_movimiento LIMIT 1");
        $stmt->execute(['id_movimiento' => $id]);
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
        // Eliminar todos los detalles de un movimiento específico
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id_movimiento = :id_movimiento");
        return $stmt->execute(['id_movimiento' => $id]);
    }

    public function buscarPorMovimiento($id_movimiento)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_movimiento = :id_movimiento");
        $stmt->execute(['id_movimiento' => $id_movimiento]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function buscarPorBien($id_bien)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_bien = :id_bien");
        $stmt->execute(['id_bien' => $id_bien]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    /**
     * Método auxiliar para buscar un detalle específico por movimiento y bien
     */
    protected function buscarPorMovimientoYBien($id_movimiento, $id_bien)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} 
             WHERE id_movimiento = :id_movimiento 
             AND id_bien = :id_bien"
        );
        $stmt->execute([
            'id_movimiento' => $id_movimiento,
            'id_bien' => $id_bien
        ]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    /**
     * Método para eliminar un detalle específico por movimiento y bien
     */
    public function eliminarDetalle($id_movimiento, $id_bien)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} 
             WHERE id_movimiento = :id_movimiento 
             AND id_bien = :id_bien"
        );
        return $stmt->execute([
            'id_movimiento' => $id_movimiento,
            'id_bien' => $id_bien
        ]);
    }
}