<?php
// src/Application/UseCase/Trabajador/DeleteTrabajadorUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;

class DeleteTrabajadorUseCase
{
    private $trabajadorRepository;

    public function __construct(TrabajadorRepositoryInterface $trabajadorRepository)
    {
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute($id)
    {
        $trabajador = $this->trabajadorRepository->getById($id);
        
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Aquí podrías agregar validaciones adicionales
        // Por ejemplo, verificar que no tenga préstamos activos

        $this->trabajadorRepository->begin();
        try {
            $result = $this->trabajadorRepository->delete($id);
            $this->trabajadorRepository->commit();
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar trabajador: " . $e->getMessage());
        }
    }
}