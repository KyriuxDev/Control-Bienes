<?php
// src/Infrastructure/Repository/MySQLBienRepository.php - VERSIÓN CORREGIDA
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
        // VALIDACIÓN ESTRICTA: Verificar si tiene ID Y si ese ID existe en BD
        $idBien = $entity->getIdBien();
        
        if ($idBien && $idBien !== '' && $idBien !== null) {
            // Verificar que el ID existe en la base de datos
            $existe = $this->obtenerPorId($idBien);
            
            if ($existe) {
                // El bien EXISTE → ACTUALIZAR
                error_log("MySQLBienRepository: Actualizando bien ID $idBien");
                return $this->actualizar($entity);
            } else {
                // El ID no existe en BD → ERROR
                throw new \Exception("No se puede actualizar: el bien con ID $idBien no existe");
            }
        } else {
            // NO tiene ID → CREAR NUEVO
            error_log("MySQLBienRepository: Creando nuevo bien");
            return $this->guardar($entity);
        }
    }

    protected function guardar($entity)
    {
        error_log("MySQLBienRepository::guardar() - Insertando nuevo registro");
        
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
            $nuevoId = $this->pdo->lastInsertId();
            $entity->setIdBien($nuevoId);
            error_log("MySQLBienRepository::guardar() - Nuevo bien creado con ID: $nuevoId");
        } else {
            error_log("MySQLBienRepository::guardar() - ERROR al insertar");
        }
        
        return $result;
    }

    protected function actualizar($entity)
    {
        $idBien = $entity->getIdBien();
        error_log("MySQLBienRepository::actualizar() - Actualizando bien ID: $idBien");
        
        $sql = "UPDATE {$this->table} 
                SET naturaleza = :naturaleza, 
                    marca = :marca, 
                    modelo = :modelo, 
                    serie = :serie, 
                    descripcion = :descripcion
                WHERE id_bien = :id_bien";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'id_bien' => $idBien,
            'naturaleza' => $entity->getNaturaleza(),
            'marca' => $entity->getMarca(),
            'modelo' => $entity->getModelo(),
            'serie' => $entity->getSerie(),
            'descripcion' => $entity->getDescripcion()
        ]);
        
        if ($result) {
            $rowsAffected = $stmt->rowCount();
            error_log("MySQLBienRepository::actualizar() - Filas afectadas: $rowsAffected");
            
            if ($rowsAffected === 0) {
                error_log("MySQLBienRepository::actualizar() - ADVERTENCIA: No se actualizó ninguna fila");
            }
        } else {
            error_log("MySQLBienRepository::actualizar() - ERROR al actualizar");
        }
        
        return $result;
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