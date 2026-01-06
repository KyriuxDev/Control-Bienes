<?php
// src/Application/UseCase/Movimiento/GetMovimientoUseCase.php
namespace App\Application\UseCase\Movimiento;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Application\DTO\MovimientoDTO;

class GetMovimientoUseCase
{
    private $movimientoRepository;

    public function __construct(MovimientoRepositoryInterface $movimientoRepository)
    {
        $this->movimientoRepository = $movimientoRepository;
    }

    public function execute($id_movimiento)
    {
        $movimiento = $this->movimientoRepository->obtenerPorId($id_movimiento);
        
        if (!$movimiento) {
            throw new \Exception("Movimiento no encontrado");
        }

        return new MovimientoDTO([
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
}