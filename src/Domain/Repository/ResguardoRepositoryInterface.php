<?php
namespace App\Domain\Repository;

interface ResguardoRepositoryInterface extends RepositoryInterface
{
    public function findByEstado($estado);
    public function findBytrabajador($trabajador_id);
    public function findByBien($bien_id);
    public function findByFolio($folio);
    public function findActivos();
}