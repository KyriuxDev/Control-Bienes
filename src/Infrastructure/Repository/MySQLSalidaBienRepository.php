<?php
// src/Domain/Repository/MySQLSalidaBienRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\SalidaBienRepositoryInterface;
use App\Domain\Entity\SalidaBien;
use PDO;

class MySQLSalidaBienRepository extends MySQLAbstractRepository implements SalidaBienRepositoryInterface
{
    protected $table = 'salida_bien';
    protected $entityClass = SalidaBien::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (folio, trabajador_id, area_origen, destino, fecha_salida, fecha_devolucion_programada, sujeto_devolucion, lugar, observaciones_estado, estado) 
                VALUES (:folio, :trabajador_id, :area_origen, :destino, :fecha_salida, :fecha_devolucion_programada, :sujeto_devolucion, :lugar, :observaciones_estado, :estado)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'area_origen' => $entity->getAreaOrigen(),
            'destino' => $entity->getDestino(),
            'fecha_salida' => $entity->getFechaSalida(),
            'fecha_devolucion_programada' => $entity->getFechaDevolucionProgramada(),
            'sujeto_devolucion' => $entity->getSujetoDevolucion(),
            'lugar' => $entity->getLugar(),
            'observaciones_estado' => $entity->getObservacionesEstado(),
            'estado' => $entity->getEstado()
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
                    area_origen = :area_origen, 
                    destino = :destino, 
                    fecha_salida = :fecha_salida, 
                    fecha_devolucion_programada = :fecha_devolucion_programada, 
                    sujeto_devolucion = :sujeto_devolucion, 
                    lugar = :lugar, 
                    observaciones_estado = :observaciones_estado, 
                    estado = :estado
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'folio' => $entity->getFolio(),
            'trabajador_id' => $entity->gettrabajadorId(),
            'area_origen' => $entity->getAreaOrigen(),
            'destino' => $entity->getDestino(),
            'fecha_salida' => $entity->getFechaSalida(),
            'fecha_devolucion_programada' => $entity->getFechaDevolucionProgramada(),
            'sujeto_devolucion' => $entity->getSujetoDevolucion(),
            'lugar' => $entity->getLugar(),
            'observaciones_estado' => $entity->getObservacionesEstado(),
            'estado' => $entity->getEstado()
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

    public function findSujetasDevolucion()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE sujeto_devolucion = TRUE");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findEnTransito()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE estado = 'EN_TRANSITO'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }
}