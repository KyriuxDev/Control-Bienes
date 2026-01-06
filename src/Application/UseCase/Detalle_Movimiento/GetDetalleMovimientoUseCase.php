<?php
// src/Application/UseCase/DetalleMovimiento/GetDetalleMovimientoUseCase.php
namespace App\Application\UseCase\DetalleMovimiento;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Application\DTO\DetalleMovimientoDTO;

class GetDetalleMovimientoUseCase
{
    private $detalleMovimientoRepository;

    public function __construct(DetalleMovimientoRepositoryInterface $detalleMovimientoRepository)
    {
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function executeByMovimiento($id_movimiento)
    {
        $detalles = $this->detalleMovimientoRepository->buscarPorMovimiento($id_movimiento);
        
        $detallesDTO = [];
        foreach ($detalles as $detalle) {
            $detallesDTO[] = new DetalleMovimientoDTO([
                'id_movimiento' => $detalle->getIdMovimiento(),
                'id_bien' => $detalle->getIdBien(),
                'cantidad' => $detalle->getCantidad(),
                'estado_fisico' => $detalle->getEstadoFisico(),
                'sujeto_devolucion' => $detalle->getSujetoDevolucion()
            ]);
        }

        return $detallesDTO;
    }

    public function executeByBien($id_bien)
    {
        $detalles = $this->detalleMovimientoRepository->buscarPorBien($id_bien);
        
        $detallesDTO = [];
        foreach ($detalles as $detalle) {
            $detallesDTO[] = new DetalleMovimientoDTO([
                'id_movimiento' => $detalle->getIdMovimiento(),
                'id_bien' => $detalle->getIdBien(),
                'cantidad' => $detalle->getCantidad(),
                'estado_fisico' => $detalle->getEstadoFisico(),
                'sujeto_devolucion' => $detalle->getSujetoDevolucion()
            ]);
        }

        return $detallesDTO;
    }
}