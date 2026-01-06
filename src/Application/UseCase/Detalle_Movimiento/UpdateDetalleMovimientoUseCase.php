<?php
// src/Application/UseCase/DetalleMovimiento/UpdateDetalleMovimientoUseCase.php
namespace App\Application\UseCase\DetalleMovimiento;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Application\DTO\DetalleMovimientoDTO;

class UpdateDetalleMovimientoUseCase
{
    private $detalleMovimientoRepository;

    public function __construct(DetalleMovimientoRepositoryInterface $detalleMovimientoRepository)
    {
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function execute(DetalleMovimientoDTO $dto)
    {
        if (!$dto->id_movimiento || !$dto->id_bien) {
            throw new \Exception("ID de movimiento e ID de bien son requeridos para actualizar");
        }

        // Buscar el detalle existente
        $detalles = $this->detalleMovimientoRepository->buscarPorMovimiento($dto->id_movimiento);
        $detalleEncontrado = null;
        
        foreach ($detalles as $detalle) {
            if ($detalle->getIdBien() == $dto->id_bien) {
                $detalleEncontrado = $detalle;
                break;
            }
        }

        if (!$detalleEncontrado) {
            throw new \Exception("Detalle de movimiento no encontrado");
        }

        // Validar cantidad si se proporciona
        if ($dto->cantidad !== null && $dto->cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        // Actualizar datos
        if ($dto->cantidad !== null) $detalleEncontrado->setCantidad($dto->cantidad);
        if ($dto->estado_fisico) $detalleEncontrado->setEstadoFisico($dto->estado_fisico);
        if ($dto->sujeto_devolucion !== null) $detalleEncontrado->setSujetoDevolucion($dto->sujeto_devolucion);

        // Guardar
        try {
            $this->detalleMovimientoRepository->persist($detalleEncontrado);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar detalle de movimiento: " . $e->getMessage());
        }
    }
}