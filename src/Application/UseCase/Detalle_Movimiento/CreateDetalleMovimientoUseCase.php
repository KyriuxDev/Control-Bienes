<?php
// src/Application/UseCase/DetalleMovimiento/CreateDetalleMovimientoUseCase.php
namespace App\Application\UseCase\DetalleMovimiento;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Domain\Repository\BienRepositoryInterface;
use App\Domain\Entity\Detalle_Movimiento;
use App\Application\DTO\DetalleMovimientoDTO;

class CreateDetalleMovimientoUseCase
{
    private $detalleMovimientoRepository;
    private $movimientoRepository;
    private $bienRepository;

    public function __construct(
        DetalleMovimientoRepositoryInterface $detalleMovimientoRepository,
        MovimientoRepositoryInterface $movimientoRepository,
        BienRepositoryInterface $bienRepository
    ) {
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
        $this->movimientoRepository = $movimientoRepository;
        $this->bienRepository = $bienRepository;
    }

    public function execute(DetalleMovimientoDTO $dto)
    {
        // Validar que el movimiento exista
        $movimiento = $this->movimientoRepository->obtenerPorId($dto->id_movimiento);
        if (!$movimiento) {
            throw new \Exception("El movimiento con ID {$dto->id_movimiento} no existe");
        }

        // Validar que el bien exista
        $bien = $this->bienRepository->obtenerPorId($dto->id_bien);
        if (!$bien) {
            throw new \Exception("El bien con ID {$dto->id_bien} no existe");
        }

        // Validar que la cantidad sea válida
        if (!$dto->cantidad || $dto->cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        // Validar que no exista ya este detalle (movimiento + bien)
        $detalles = $this->detalleMovimientoRepository->buscarPorMovimiento($dto->id_movimiento);
        foreach ($detalles as $detalle) {
            if ($detalle->getIdBien() == $dto->id_bien) {
                throw new \Exception("Ya existe un detalle para este bien en el movimiento");
            }
        }

        // Crear la entidad
        $detalle = new Detalle_Movimiento();
        $detalle->setIdMovimiento($dto->id_movimiento)
                ->setIdBien($dto->id_bien)
                ->setCantidad($dto->cantidad)
                ->setEstadoFisico($dto->estado_fisico)
                ->setSujetoDevolucion($dto->sujeto_devolucion);

        // Guardar
        try {
            $this->detalleMovimientoRepository->persist($detalle);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear detalle de movimiento: " . $e->getMessage());
        }
    }
}