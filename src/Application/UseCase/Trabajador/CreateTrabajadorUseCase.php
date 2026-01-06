<?php
// src/Application/UseCase/Trabajador/CreateTrabajadorUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\Trabajador;
use App\Application\DTO\TrabajadorDTO;

class CreateTrabajadorUseCase
{
    private $trabajadorRepository;

    public function __construct(TrabajadorRepositoryInterface $trabajadorRepository)
    {
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute(TrabajadorDTO $dto)
    {
        // Validar que el nombre no esté vacío
        if (empty($dto->nombre)) {
            throw new \Exception("El nombre es obligatorio");
        }

        // Validar que la matrícula no esté vacía
        if (empty($dto->matricula)) {
            throw new \Exception("La matrícula es obligatoria");
        }

        // Validar que la matrícula no exista
        $existente = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula);
        if ($existente) {
            throw new \Exception("La matrícula {$dto->matricula} ya existe");
        }

        // Validar formato de teléfono si se proporciona
        if ($dto->telefono && !preg_match('/^[0-9]{10}$/', $dto->telefono)) {
            throw new \Exception("El teléfono debe contener 10 dígitos numéricos");
        }

        // Crear la entidad
        $trabajador = new Trabajador();
        $trabajador->setMatricula($dto->matricula)
                   ->setNombre($dto->nombre)
                   ->setCargo($dto->cargo)
                   ->setInstitucion($dto->institucion)
                   ->setAdscripcion($dto->adscripcion)
                   ->setIdentificacion($dto->identificacion)
                   ->setTelefono($dto->telefono);

        // Guardar
        try {
            $this->trabajadorRepository->persist($trabajador);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear trabajador: " . $e->getMessage());
        }
    }
}