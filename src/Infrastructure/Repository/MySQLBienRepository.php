<?php
// src/Infrastructure/Repository/MySQLBienRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\BienRepositoryInterface;
use App\Domain\Entity\Bien;
use PDO;

class MySQLBienRepository implements BienRepositoryInterface
{
    protected $pdo;
    protected $table = 'bien';
    protected $entityClass = Bien::class;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function persist($entity)
    {
        if ($entity->getIdBien()) {
            return $this->actualizar($entity);
        } else {
            return $this->guardar($entity);
        }
    }

    protected function guardar($entity)
    {
        $sql = "INSERT INTO {$this->table} (naturaleza, marca, modelo, serie, descripcion) 
                VALUES (:naturaleza, :marca, :modelo, :serie, :descripcion)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'naturaleza' => $entity->getNaturaleza(),
            'marca' => $entity->getMarca(),
            'modelo' => $entity->getModelo(),
            'serie' => $entity->getSerie(),
            'descripcion' => $entity->getDescripcion()
        ]);
        
        if ($result) {
            $entity->setIdBien($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function actualizar($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET naturaleza = :naturaleza, 
                    marca = :marca, 
                    modelo = :modelo, 
                    serie = :serie, 
                    descripcion = :descripcion
                WHERE id_bien = :id_bien";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id_bien' => $entity->getIdBien(),
            'naturaleza' => $entity->getNaturaleza(),
            'marca' => $entity->getMarca(),
            'modelo' => $entity->getModelo(),
            'serie' => $entity->getSerie(),
            'descripcion' => $entity->getDescripcion()
        ]);
    }

    public function obtenerPorId($id_bien)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_bien = :id_bien");
        $stmt->execute(['id_bien' => $id_bien]);
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
        return $stmt->execute(['id_bien' => $id]);
    }

    public function buscarPorNaturaleza($naturaleza)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE naturaleza = :naturaleza");
        $stmt->execute(['naturaleza' => $naturaleza]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }
}