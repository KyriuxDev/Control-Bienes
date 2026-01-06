<?php
// src/Application/UseCase/DetalleMovimiento/ListDetallesMovimientoUseCase.php
namespace App\Application\UseCase\DetalleMovimiento;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;
use App\Application\DTO\DetalleMovimientoDTO;

class ListDetallesMovimientoUseCase
{
    private $detalleMovimientoRepository;

    public function __construct(DetalleMovimientoRepositoryInterface $detalleMovimientoRepository)
    {
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function execute()
    {
        $detalles = $this->detalleMovimientoRepository->obtenerTodos();
        
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