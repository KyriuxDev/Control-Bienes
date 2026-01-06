<?php
namespace App\Domain\Repository;

interface SalidaDetalleRepositoryInterface extends RepositoryInterface
{
    public function findBySalida($salida_id);
    public function findByBien($bien_id);
    public function deleteBySalida($salida_id);
}