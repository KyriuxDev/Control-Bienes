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

    public function execute($id)
    {
        $trabajador = $this->trabajadorRepository->getById($id);
        
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        return new TrabajadorDTO([
            'id' => $trabajador->getId(),
            'nombre' => $trabajador->getNombre(),
            'cargo' => $trabajador->getCargo(),
            'institucion' => $trabajador->getInstitucion(),
            'adscripcion' => $trabajador->getAdscripcion(),
            'matricula' => $trabajador->getMatricula(),
            'identificacion' => $trabajador->getIdentificacion(),
            'direccion' => $trabajador->getDireccion(),
            'telefono' => $trabajador->getTelefono(),
            'fecha_registro' => $trabajador->getFechaRegistro()
        ]);
    }

    public function executeByMatricula($matricula)
    {
        $trabajador = $this->trabajadorRepository->findByMatricula($matricula);
        
        if (!$trabajador) {
            throw new \Exception("Trabajador con matrícula {$matricula} no encontrado");
        }

        return new TrabajadorDTO([
            'id' => $trabajador->getId(),
            'nombre' => $trabajador->getNombre(),
            'cargo' => $trabajador->getCargo(),
            'institucion' => $trabajador->getInstitucion(),
            'adscripcion' => $trabajador->getAdscripcion(),
            'matricula' => $trabajador->getMatricula(),
            'identificacion' => $trabajador->getIdentificacion(),
            'direccion' => $trabajador->getDireccion(),
            'telefono' => $trabajador->getTelefono(),
            'fecha_registro' => $trabajador->getFechaRegistro()
        ]);
    }
}