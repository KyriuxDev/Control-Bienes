<?php
// src/Application/UseCase/Bien/DeleteBienUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Domain\Repository\DetalleMovimientoRepositoryInterface;

class DeleteBienUseCase
{
    private $bienRepository;
    private $detalleMovimientoRepository;

    public function __construct(
        BienRepositoryInterface $bienRepository,
        DetalleMovimientoRepositoryInterface $detalleMovimientoRepository
    ) {
        $this->bienRepository = $bienRepository;
        $this->detalleMovimientoRepository = $detalleMovimientoRepository;
    }

    public function execute($id_bien)
    {
        $bien = $this->bienRepository->obtenerPorId($id_bien);
        
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        // Verificar si el bien está en movimientos (detalles)
        $detalles = $this->detalleMovimientoRepository->buscarPorBien($id_bien);
        if (!empty($detalles)) {
            throw new \Exception("No se puede eliminar el bien porque está asociado a movimientos");
        }

        try {
            $result = $this->bienRepository->eliminar($id_bien);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar bien: " . $e->getMessage());
        }
    }
}