<?php

namespace App\Domain\Repository;

interface TrabajadorRepositoryInterface 
{
    public function obtenerPorMatricula($matricula);
}