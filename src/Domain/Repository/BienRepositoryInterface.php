<?php
// src/Domain/Repository/BienRepositoryInterface.php
namespace App\Domain\Repository;

use App\Domain\Entity\Bien;

interface BienRepositoryInterface 
{
    public function obtenerPorId($id);
    public function obtenerTodos();
    public function persist($entity); //GUARDAR Y ACTUALIZAR (CREATE & UPDATE)
    public function eliminar($id);
    public function buscarPorNaturaleza($naturaleza);
}