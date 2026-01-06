<?php
// src/Domain/Repository/DetalleMovimientoRepositoryInterface.php
namespace App\Domain\Repository;

use App\Domain\Entity\Detalle_Movimiento;

interface DetalleMovimientoRepositoryInterface 
{
    public function obtenerPorId($id);
    public function obtenerTodos();
    public function persist($entity);
    public function eliminar($id);
    public function buscarPorMovimiento($id_movimiento);
    public function buscarPorBien($id_bien);
}