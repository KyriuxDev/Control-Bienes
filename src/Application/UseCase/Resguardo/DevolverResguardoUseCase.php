<?php
// src/Application/UseCase/Resguardo/DevolverResguardoUseCase.php
namespace App\Application\UseCase\Resguardo;

use App\Domain\Repository\ResguardoRepositoryInterface;

class DevolverResguardoUseCase
{
    private $resguardoRepository;

    public function __construct(ResguardoRepositoryInterface $resguardoRepository)
    {
        $this->resguardoRepository = $resguardoRepository;
    }

    public function execute($id, $fechaDevolucion = null)
    {
        $resguardo = $this->resguardoRepository->getById($id);
        
        if (!$resguardo) {
            throw new \Exception("Resguardo no encontrado");
        }

        if ($resguardo->getEstado() === 'DEVUELTO') {
            throw new \Exception("Este resguardo ya fue devuelto");
        }

        // Establecer la fecha de devolución
        $fecha = isset($fechaDevolucion) ? $fechaDevolucion : date('Y-m-d');
        $resguardo->setFechaDevolucion($fecha);
        $resguardo->setEstado('DEVUELTO');

        // Guardar
        $this->resguardoRepository->begin();
        try {
            $this->resguardoRepository->persist($resguardo);
            $this->resguardoRepository->commit();
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error al devolver resguardo: " . $e->getMessage());
        }
    }
}
