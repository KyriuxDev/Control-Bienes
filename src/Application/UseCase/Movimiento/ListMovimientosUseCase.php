<?php
// src/Application/UseCase/Movimiento/ListMovimientosUseCase.php
namespace App\Application\UseCase\Movimiento;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Application\DTO\MovimientoDTO;

class ListMovimientosUseCase
{
    private $movimientoRepository;

    public function __construct(MovimientoRepositoryInterface $movimientoRepository)
    {
        $this->movimientoRepository = $movimientoRepository;
    }

    public function execute()
    {
        $movimientos = $this->movimientoRepository->obtenerTodos();
        
        $movimientosDTO = [];
        foreach ($movimientos as $movimiento) {
            $movimientosDTO[] = new MovimientoDTO([
                'id_movimiento' => $movimiento->getIdMovimiento(),
                'tipo_movimiento' => $movimiento->getTipoMovimiento(),
                'matricula_recibe' => $movimiento->getMatriculaRecibe(),
                'matricula_entrega' => $movimiento->getMatriculaEntrega(),
                'fecha' => $movimiento->getFecha(),
                'lugar' => $movimiento->getLugar(),
                'area' => $movimiento->getArea(),
                'folio' => $movimiento->getFolio(),
                'dias_prestamo' => $movimiento->getDiasPrestamo()
            ]);
        }

        return $movimientosDTO;
    }
}