<?php
namespace App\Domain\Repository;

interface PrestamoRepositoryInterface extends RepositoryInterface
{
    public function findByEstado($estado);
    public function findBytrabajador($trabajador_id);
    public function findByFolio($folio);
    public function findVencidos();
}