<?php
// src/Application/UseCase/Trabajador/ListTrabajadoresUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Application\DTO\TrabajadorDTO;

class ListTrabajadoresUseCase
{
    private $trabajadorRepository;

    public function __construct(TrabajadorRepositoryInterface $trabajadorRepository)
    {
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute()
    {
        $trabajadores = $this->trabajadorRepository->obtenerTodos();
        
        $trabajadoresDTO = [];
        foreach ($trabajadores as $trabajador) {
            $trabajadoresDTO[] = new TrabajadorDTO([
                'matricula' => $trabajador->getMatricula(),
                'nombre' => $trabajador->getNombre(),
                'cargo' => $trabajador->getCargo(),
                'institucion' => $trabajador->getInstitucion(),
                'adscripcion' => $trabajador->getAdscripcion(),
                'identificacion' => $trabajador->getIdentificacion(),
                'telefono' => $trabajador->getTelefono()
            ]);
        }

        return $trabajadoresDTO;
    }
}