<?php
// src/Application/UseCase/DetalleMovimiento/DeleteDetalleMovimientoUseCase.php
namespace App\Application\UseCase\DetalleMovimiento;

use App\Domain\Repository\DetalleMovimientoRepositoryInterface;

class DeleteDetalleMovimientoUseCase
{
    private $detalleMovimientoRepository;

    public function __construct(DetalleMovimientoRepositoryInterface $detalleMovimientoRepository)
    {
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function execute($id_movimiento, $id_bien)
    {
        // Buscar el detalle
        $detalles = $this->detalleMovimientoRepository->buscarPorMovimiento($id_movimiento);
        $detalleEncontrado = false;
        
        foreach ($detalles as $detalle) {
            if ($detalle->getIdBien() == $id_bien) {
                $detalleEncontrado = true;
                break;
            }
        }

        if (!$detalleEncontrado) {
            throw new \Exception("Detalle de movimiento no encontrado");
        }

        try {
            // Usar el método eliminarDetalle del repositorio
            $result = $this->detalleMovimientoRepository->eliminarDetalle($id_movimiento, $id_bien);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar detalle de movimiento: " . $e->getMessage());
        }
    }

    public function executeByMovimiento($id_movimiento)
    {
        // Eliminar todos los detalles de un movimiento
        try {
            $result = $this->detalleMovimientoRepository->eliminar($id_movimiento);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar detalles del movimiento: " . $e->getMessage());
        }
    }
}
