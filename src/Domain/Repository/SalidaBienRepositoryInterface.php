<?php
namespace App\Domain\Repository;

interface SalidaBienRepositoryInterface extends RepositoryInterface
{
    public function findByEstado($estado);
    public function findBytrabajador($trabajador_id);
    public function findByFolio($folio);
    public function findSujetasDevolucion();
    public function findEnTransito();
}