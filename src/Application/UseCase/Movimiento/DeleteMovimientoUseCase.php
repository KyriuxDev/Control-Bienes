<?php
// src/Application/UseCase/Movimiento/DeleteMovimientoUseCase.php
namespace App\Application\UseCase\Movimiento;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Domain\Repository\DetalleMovimientoRepositoryInterface;

class DeleteMovimientoUseCase
{
    private $movimientoRepository;
    private $detalleMovimientoRepository;

    public function __construct(
        MovimientoRepositoryInterface $movimientoRepository,
        DetalleMovimientoRepositoryInterface $detalleMovimientoRepository
    ) {
        $this->movimientoRepository = $movimientoRepository;
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function execute($id_movimiento)
    {
        $movimiento = $this->movimientoRepository->obtenerPorId($id_movimiento);
        
        if (!$movimiento) {
            throw new \Exception("Movimiento no encontrado");
        }

        // Verificar si tiene detalles asociados
        $detalles = $this->detalleMovimientoRepository->buscarPorMovimiento($id_movimiento);
        if (!empty($detalles)) {
            throw new \Exception("No se puede eliminar el movimiento porque tiene detalles asociados. Elimine primero los detalles.");
        }

        try {
            $result = $this->movimientoRepository->eliminar($id_movimiento);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar movimiento: " . $e->getMessage());
        }
    }
}