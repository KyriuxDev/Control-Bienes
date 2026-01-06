<?php
// src/Domain/Repository/TrabajadorRepositoryInterface.php
namespace App\Domain\Repository;

use App\Domain\Entity\Trabajador;

interface TrabajadorRepositoryInterface 
{
    public function obtenerPorMatricula($matricula);
    public function obtenerTodos();
    public function persist($entity);
    public function eliminar($matricula);
}