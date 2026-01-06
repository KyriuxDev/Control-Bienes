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
        $trabajadores = $this->trabajadorRepository->getAll();
        
        $trabajadoresDTO = [];
        foreach ($trabajadores as $trabajador) {
            $trabajadoresDTO[] = new TrabajadorDTO([
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

        return $trabajadoresDTO;
    }
}