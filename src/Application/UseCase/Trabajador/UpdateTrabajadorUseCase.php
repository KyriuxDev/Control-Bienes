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
        if (!$dto->matricula) {
            throw new \Exception("Matrícula de trabajador requerida para actualizar");
        }

        // Buscar el trabajador existente
        $trabajador = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula);
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Validar formato de teléfono si se proporciona
        if ($dto->telefono && !preg_match('/^[0-9]{10}$/', $dto->telefono)) {
            throw new \Exception("El teléfono debe contener 10 dígitos numéricos");
        }

        // Actualizar datos
        if ($dto->nombre) $trabajador->setNombre($dto->nombre);
        if ($dto->cargo) $trabajador->setCargo($dto->cargo);
        if ($dto->institucion) $trabajador->setInstitucion($dto->institucion);
        if ($dto->adscripcion) $trabajador->setAdscripcion($dto->adscripcion);
        if ($dto->identificacion) $trabajador->setIdentificacion($dto->identificacion);
        if ($dto->telefono) $trabajador->setTelefono($dto->telefono);

        // Guardar
        try {
            $this->trabajadorRepository->persist($trabajador);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar trabajador: " . $e->getMessage());
        }
    }
}