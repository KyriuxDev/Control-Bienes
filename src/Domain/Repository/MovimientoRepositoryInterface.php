<?php
// src/Domain/Repository/MovimientoRepositoryInterface.php
namespace App\Domain\Repository;

use App\Domain\Entity\Movimiento;

interface MovimientoRepositoryInterface 
{
    public function obtenerPorId($id);
    public function obtenerTodos();
    public function persist($entity);
    public function eliminar($id);
}