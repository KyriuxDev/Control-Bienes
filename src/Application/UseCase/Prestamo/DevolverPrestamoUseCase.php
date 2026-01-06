<?php
// src/Application/UseCase/Prestamo/DevolverPrestamoUseCase.php
namespace App\Application\UseCase\Prestamo;

use App\Domain\Repository\PrestamoRepositoryInterface;

class DevolverPrestamoUseCase
{
    private $prestamoRepository;

    public function __construct(PrestamoRepositoryInterface $prestamoRepository)
    {
        $this->prestamoRepository = $prestamoRepository;
    }

    public function execute($id, $fechaDevolucion = null)
    {
        $prestamo = $this->prestamoRepository->getById($id);
        
        if (!$prestamo) {
            throw new \Exception("Préstamo no encontrado");
        }

        if ($prestamo->getEstado() === 'DEVUELTO') {
            throw new \Exception("Este préstamo ya fue devuelto");
        }

        // Establecer la fecha de devolución real
        $fecha = isset($fechaDevolucion) ? $fechaDevolucion : date('Y-m-d');
        $prestamo->setFechaDevolucionReal($fecha);
        $prestamo->setEstado('DEVUELTO');

        // Guardar
        $this->prestamoRepository->begin();
        try {
            $this->prestamoRepository->persist($prestamo);
            $this->prestamoRepository->commit();
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al devolver préstamo: " . $e->getMessage());
        }
    }
}
