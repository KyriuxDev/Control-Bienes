<?php
// src/Infrastructure/Repository/MySQLTrabajadorRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\Trabajador;
use PDO;

class MySQLTrabajadorRepository implements TrabajadorRepositoryInterface
{
    protected $pdo;
    protected $table = 'trabajador';
    protected $entityClass = Trabajador::class;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function persist($entity)
    {
        if ($entity->getMatricula()) {
            return $this->actualizar($entity);
        } else {
            return $this->guardar($entity);
        }
    }

    protected function guardar($entity)
    {
        $sql = "INSERT INTO {$this->table} (matricula, nombre, institucion, adscripcion, identificacion, telefono, cargo) 
                VALUES (:matricula, :nombre, :institucion, :adscripcion, :identificacion, :telefono, :cargo)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'matricula' => $entity->getMatricula(),
            'nombre' => $entity->getNombre(),
            'institucion' => $entity->getInstitucion(),
            'adscripcion' => $entity->getAdscripcion(),
            'identificacion' => $entity->getIdentificacion(),
            'telefono' => $entity->getTelefono(),
            'cargo' => $entity->getCargo()
        ]);
        
        if ($result) {
            $entity->setMatricula($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function actualizar($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET matricula = :matricula,
                    nombre = :nombre, 
                    institucion = :institucion, 
                    adscripcion = :adscripcion, 
                    identificacion = :identificacion,
                    telefono = :telefono,
                    cargo = :cargo
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'matricula' => $entity->getId(),
            'nombre' => $entity->getNombre(),
            'institucion' => $entity->getInstitucion(),
            'adscripcion' => $entity->getAdscripcion(),
            'identificacion' => $entity->getIdentificacion(),
            'telefono' => $entity->getTelefono(),
            'cargo' => $entity->getCargo(),
        ]);
    }

    public function obtenerPorMatricula($matricula)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE matricula = :matricula");
        $stmt->execute(['matricula' => $matricula]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function eliminar($matricula)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE matricula = :matricula");
        return $stmt->execute(['matricula' => $matricula]);
    }

}