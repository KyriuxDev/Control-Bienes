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
        // Validar que la matrícula no exista
        $existente = $this->trabajadorRepository->findByMatricula($dto->matricula);
        if ($existente) {
            throw new \Exception("La matrícula {$dto->matricula} ya existe");
        }

        // Crear la entidad
        $trabajador = new Trabajador();
        $trabajador->setNombre($dto->nombre)
                 ->setCargo($dto->cargo)
                 ->setInstitucion($dto->institucion)
                 ->setAdscripcion($dto->adscripcion)
                 ->setMatricula($dto->matricula)
                 ->setIdentificacion($dto->identificacion)
                 ->setDireccion($dto->direccion)
                 ->setTelefono($dto->telefono);

        // Guardar
        $this->trabajadorRepository->begin();
        try {
            $this->trabajadorRepository->persist($trabajador);
            $this->trabajadorRepository->commit();
            
            // Retornar DTO con el ID generado
            $dto->id = $trabajador->getId();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear trabajador: " . $e->getMessage());
        }
    }
}