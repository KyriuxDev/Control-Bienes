<?php
namespace App\Domain\Repository;

interface PrestamoDetalleRepositoryInterface extends RepositoryInterface
{
    public function findByPrestamo($prestamo_id);
    public function findByBien($bien_id);
    public function deleteByPrestamo($prestamo_id);
}