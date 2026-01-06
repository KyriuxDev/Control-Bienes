<?php
// src/Application/UseCase/Bien/DeleteBienUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;

class DeleteBienUseCase
{
    private $bienRepository;

    public function __construct(BienRepositoryInterface $bienRepository)
    {
        $this->bienRepository = $bienRepository;
    }

    public function execute($id)
    {
        $bien = $this->bienRepository->getById($id);
        
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        // Aquí podrías agregar validaciones adicionales
        // Por ejemplo, verificar que no esté en préstamos activos o resguardos

        $this->bienRepository->begin();
        try {
            $result = $this->bienRepository->delete($id);
            $this->bienRepository->commit();
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar bien: " . $e->getMessage());
        }
    }
}
