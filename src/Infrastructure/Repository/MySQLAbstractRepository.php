<?php
// src/Domain/Repository/MySQLAbstractRepository.php
namespace App\Infrastructure\Repository;
use App\Domain\Repository\RepositoryInterface;
use PDO;

abstract class MySQLAbstractRepository implements RepositoryInterface
{
    protected $pdo;
    protected $table;
    protected $entityClass;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->entityClass);
    }

    public function persist($entity)
    {
        if ($entity->getId()) {
            return $this->update($entity);
        } else {
            return $this->save($entity);
        }
    }

    public function begin()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    abstract protected function save($entity);
    abstract protected function update($entity);
}