<?php
// src/Application/UseCase/SalidaBien/UpdateSalidaBienUseCase.php
namespace App\Application\UseCase\SalidaBien;

use App\Domain\Repository\SalidaBienRepositoryInterface;
use App\Application\DTO\SalidaBienDTO;

class UpdateSalidaBienUseCase
{
    private $salidaBienRepository;

    public function __construct(SalidaBienRepositoryInterface $salidaBienRepository)
    {
        $this->salidaBienRepository = $salidaBienRepository;
    }

    public function execute(SalidaBienDTO $dto)
    {
        if (!$dto->id) {
            throw new \Exception("ID de salida de bien requerido para actualizar");
        }

        // Buscar la salida existente
        $salidaBien = $this->salidaBienRepository->getById($dto->id);
        if (!$salidaBien) {
            throw new \Exception("Salida de bien no encontrada");
        }

        // Validar folio si cambió
        if ($dto->folio && $dto->folio !== $salidaBien->getFolio()) {
            $existente = $this->salidaBienRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }

        // Actualizar datos
        if ($dto->folio) $salidaBien->setFolio($dto->folio);
        if ($dto->trabajador_id) $salidaBien->setTrabajadorId($dto->trabajador_id);
        if ($dto->area_origen) $salidaBien->setAreaOrigen($dto->area_origen);
        if ($dto->destino) $salidaBien->setDestino($dto->destino);
        if ($dto->fecha_salida) $salidaBien->setFechaSalida($dto->fecha_salida);
        if ($dto->fecha_devolucion_programada !== null) $salidaBien->setFechaDevolucionProgramada($dto->fecha_devolucion_programada);
        if ($dto->sujeto_devolucion !== null) $salidaBien->setSujetoDevolucion($dto->sujeto_devolucion);
        if ($dto->lugar) $salidaBien->setLugar($dto->lugar);
        if ($dto->observaciones_estado !== null) $salidaBien->setObservacionesEstado($dto->observaciones_estado);
        if ($dto->estado) $salidaBien->setEstado($dto->estado);

        // Guardar
        $this->salidaBienRepository->begin();
        try {
            $this->salidaBienRepository->persist($salidaBien);
            $this->salidaBienRepository->commit();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar salida de bien: " . $e->getMessage());
        }
    }
}
