<?php
namespace App\Domain\Repository;

interface BienRepositoryInterface 
{
    public function obtenerPorId($id_bien);
    public function obtenerTodos();
    public function guardar(Bien $bien);
}