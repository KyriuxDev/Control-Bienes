<?php
// src/Application/UseCase/Trabajador/GetTrabajadorUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Application\DTO\TrabajadorDTO;

class GetTrabajadorUseCase
{
    private $trabajadorRepository;

    public function __construct(TrabajadorRepositoryInterface $trabajadorRepository)
    {
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute($matricula)
    {
        $trabajador = $this->trabajadorRepository->obtenerPorMatricula($matricula);
        
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        return new TrabajadorDTO([
            'matricula' => $trabajador->getMatricula(),
            'nombre' => $trabajador->getNombre(),
            'cargo' => $trabajador->getCargo(),
            'institucion' => $trabajador->getInstitucion(),
            'adscripcion' => $trabajador->getAdscripcion(),
            'identificacion' => $trabajador->getIdentificacion(),
            'telefono' => $trabajador->getTelefono()
        ]);
    }
}