<?php
namespace App\Domain\Repository;

interface TrabajadorRepositoryInterface extends RepositoryInterface
{
    public function findByMatricula($matricula);
}