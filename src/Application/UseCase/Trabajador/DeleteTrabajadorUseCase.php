<?php
// src/Application/UseCase/Trabajador/DeleteTrabajadorUseCase.php
namespace App\Application\UseCase\Trabajador;

use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Repository\MovimientoRepositoryInterface;

class DeleteTrabajadorUseCase
{
    private $trabajadorRepository;
    private $movimientoRepository;

    public function __construct(
        TrabajadorRepositoryInterface $trabajadorRepository,
        MovimientoRepositoryInterface $movimientoRepository
    ) {
        $this->trabajadorRepository = $trabajadorRepository;
        $this->movimientoRepository = $movimientoRepository;
    }

    public function execute($matricula)
    {
        $trabajador = $this->trabajadorRepository->obtenerPorMatricula($matricula);
        
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Verificar si el trabajador tiene movimientos asociados
        $movimientos = $this->movimientoRepository->obtenerTodos();
        foreach ($movimientos as $movimiento) {
            if ($movimiento->getMatriculaRecibe() == $matricula || 
                $movimiento->getMatriculaEntrega() == $matricula) {
                throw new \Exception("No se puede eliminar el trabajador porque tiene movimientos asociados");
            }
        }

        try {
            $result = $this->trabajadorRepository->eliminar($matricula);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar trabajador: " . $e->getMessage());
        }
    }
}