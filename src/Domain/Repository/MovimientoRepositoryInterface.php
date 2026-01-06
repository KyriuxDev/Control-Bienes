<?php
namespace App\Domain\Repository;

interface MovimientoRepositoryInterface 
{
    public function obtenerPorId($id_movimiento);
    public function obtenerTodos();
}