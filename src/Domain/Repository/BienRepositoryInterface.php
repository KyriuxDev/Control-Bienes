<?php
namespace App\Domain\Repository;

interface BienRepositoryInterface extends RepositoryInterface
{
    public function findByNaturaleza($naturaleza);
    public function findByIdentificacion($identificacion);
}