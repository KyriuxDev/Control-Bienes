<?php
// src/Domain/Repository/MySQLBienRepository.php
namespace App\Domain\Repository;

use App\Domain\Entity\Bien;
use PDO;

class MySQLBienRepository extends MySQLAbstractRepository implements BienRepositoryInterface
{
    protected $table = 'bien';
    protected $entityClass = Bien::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (identificacion, descripcion, marca, modelo, serie, naturaleza, estado_fisico) 
                VALUES (:identificacion, :descripcion, :marca, :modelo, :serie, :naturaleza, :estado_fisico)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'identificacion' => $entity->getIdentificacion(),
            'descripcion' => $entity->getDescripcion(),
            'marca' => $entity->getMarca(),
            'modelo' => $entity->getModelo(),
            'serie' => $entity->getSerie(),
            'naturaleza' => $entity->getNaturaleza(),
            'estado_fisico' => $entity->getEstadoFisico()
        ]);
        
        if ($result) {
            $entity->setId($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function update($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET identificacion = :identificacion, 
                    descripcion = :descripcion, 
                    marca = :marca, 
                    modelo = :modelo, 
                    serie = :serie, 
                    naturaleza = :naturaleza, 
                    estado_fisico = :estado_fisico
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'identificacion' => $entity->getIdentificacion(),
            'descripcion' => $entity->getDescripcion(),
            'marca' => $entity->getMarca(),
            'modelo' => $entity->getModelo(),
            'serie' => $entity->getSerie(),
            'naturaleza' => $entity->getNaturaleza(),
            'estado_fisico' => $entity->getEstadoFisico()
        ]);
    }

    public function findByNaturaleza($naturaleza)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE naturaleza = :naturaleza");
        $stmt->execute(['naturaleza' => $naturaleza]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function findByIdentificacion($identificacion)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE identificacion = :identificacion");
        $stmt->execute(['identificacion' => $identificacion]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }
}