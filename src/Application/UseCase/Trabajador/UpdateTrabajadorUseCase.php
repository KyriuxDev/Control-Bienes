<?php
// src/Application/UseCase/Trabajador/UpdateTrabajadorUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Application\DTO\TrabajadorDTO;

class UpdateTrabajadorUseCase
{
    private $trabajadorRepository;

    public function __construct(TrabajadorRepositoryInterface $trabajadorRepository)
    {
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute(TrabajadorDTO $dto)
    {
        if (!$dto->id) {
            throw new \Exception("ID de trabajador requerido para actualizar");
        }

        // Buscar el trabajador existente
        $trabajador = $this->trabajadorRepository->getById($dto->id);
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Validar matrícula si cambió
        if ($dto->matricula && $dto->matricula !== $trabajador->getMatricula()) {
            $existente = $this->trabajadorRepository->findByMatricula($dto->matricula);
            if ($existente) {
                throw new \Exception("La matrícula {$dto->matricula} ya existe");
            }
        }

        // Actualizar datos
        if ($dto->nombre) $trabajador->setNombre($dto->nombre);
        if ($dto->cargo) $trabajador->setCargo($dto->cargo);
        if ($dto->institucion) $trabajador->setInstitucion($dto->institucion);
        if ($dto->adscripcion) $trabajador->setAdscripcion($dto->adscripcion);
        if ($dto->matricula) $trabajador->setMatricula($dto->matricula);
        if ($dto->identificacion) $trabajador->setIdentificacion($dto->identificacion);
        if ($dto->direccion) $trabajador->setDireccion($dto->direccion);
        if ($dto->telefono) $trabajador->setTelefono($dto->telefono);

        // Guardar
        $this->trabajadorRepository->begin();
        try {
            $this->trabajadorRepository->persist($trabajador);
            $this->trabajadorRepository->commit();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar trabajador: " . $e->getMessage());
        }
    }
}