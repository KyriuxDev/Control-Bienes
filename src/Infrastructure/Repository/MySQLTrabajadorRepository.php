<?php
// src/Infrastructure/Repository/MySQLTrabajadorRepository.php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\Trabajador;
use PDO;

class MySQLTrabajadorRepository extends MySQLAbstractRepository implements TrabajadorRepositoryInterface
{
    protected $table = 'empleados';
    protected $entityClass = Trabajador::class;

    protected function save($entity)
    {
        $sql = "INSERT INTO {$this->table} (nombre, cargo, institucion, adscripcion, matricula, identificacion, direccion, telefono) 
                VALUES (:nombre, :cargo, :institucion, :adscripcion, :matricula, :identificacion, :direccion, :telefono)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'nombre' => $entity->getNombre(),
            'cargo' => $entity->getCargo(),
            'institucion' => $entity->getInstitucion(),
            'adscripcion' => $entity->getAdscripcion(),
            'matricula' => $entity->getMatricula(),
            'identificacion' => $entity->getIdentificacion(),
            'direccion' => $entity->getDireccion(),
            'telefono' => $entity->getTelefono()
        ]);
        
        if ($result) {
            $entity->setId($this->pdo->lastInsertId());
        }
        
        return $result;
    }

    protected function update($entity)
    {
        $sql = "UPDATE {$this->table} 
                SET nombre = :nombre, 
                    cargo = :cargo, 
                    institucion = :institucion, 
                    adscripcion = :adscripcion, 
                    matricula = :matricula, 
                    identificacion = :identificacion, 
                    direccion = :direccion, 
                    telefono = :telefono
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $entity->getId(),
            'nombre' => $entity->getNombre(),
            'cargo' => $entity->getCargo(),
            'institucion' => $entity->getInstitucion(),
            'adscripcion' => $entity->getAdscripcion(),
            'matricula' => $entity->getMatricula(),
            'identificacion' => $entity->getIdentificacion(),
            'direccion' => $entity->getDireccion(),
            'telefono' => $entity->getTelefono()
        ]);
    }

    public function findByMatricula($matricula)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE matricula = :matricula");
        $stmt->execute(['matricula' => $matricula]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }
}