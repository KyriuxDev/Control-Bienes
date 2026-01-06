<?php
// src/Application/UseCase/Movimiento/UpdateMovimientoUseCase.php
namespace App\Application\UseCase\Movimiento;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Application\DTO\MovimientoDTO;

class UpdateMovimientoUseCase
{
    private $movimientoRepository;
    private $trabajadorRepository;

    public function __construct(
        MovimientoRepositoryInterface $movimientoRepository,
        TrabajadorRepositoryInterface $trabajadorRepository
    ) {
        $this->movimientoRepository = $movimientoRepository;
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute(MovimientoDTO $dto)
    {
        if (!$dto->id_movimiento) {
            throw new \Exception("ID de movimiento requerido para actualizar");
        }

        // Buscar el movimiento existente
        $movimiento = $this->movimientoRepository->obtenerPorId($dto->id_movimiento);
        if (!$movimiento) {
            throw new \Exception("Movimiento no encontrado");
        }

        // Validar tipo de movimiento si se proporciona
        if ($dto->tipo_movimiento) {
            $tiposValidos = ['Prestamo', 'Resguardo', 'Transferencia', 'Devolucion'];
            if (!in_array($dto->tipo_movimiento, $tiposValidos)) {
                throw new \Exception("Tipo de movimiento inválido");
            }
        }

        // Validar trabajadores si se proporcionan
        if ($dto->matricula_recibe && $dto->matricula_recibe !== $movimiento->getMatriculaRecibe()) {
            $trabajadorRecibe = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula_recibe);
            if (!$trabajadorRecibe) {
                throw new \Exception("El trabajador con matrícula {$dto->matricula_recibe} no existe");
            }
        }

        if ($dto->matricula_entrega && $dto->matricula_entrega !== $movimiento->getMatriculaEntrega()) {
            $trabajadorEntrega = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula_entrega);
            if (!$trabajadorEntrega) {
                throw new \Exception("El trabajador con matrícula {$dto->matricula_entrega} no existe");
            }
        }

        // Actualizar datos
        if ($dto->tipo_movimiento) $movimiento->setTipoMovimiento($dto->tipo_movimiento);
        if ($dto->matricula_recibe) $movimiento->setMatriculaRecibe($dto->matricula_recibe);
        if ($dto->matricula_entrega) $movimiento->setMatriculaEntrega($dto->matricula_entrega);
        if ($dto->fecha) $movimiento->setFecha($dto->fecha);
        if ($dto->lugar) $movimiento->setLugar($dto->lugar);
        if ($dto->area) $movimiento->setArea($dto->area);
        if ($dto->folio) $movimiento->setFolio($dto->folio);
        if ($dto->dias_prestamo !== null) $movimiento->setDiasPrestamo($dto->dias_prestamo);

        // Guardar
        try {
            $this->movimientoRepository->persist($movimiento);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar movimiento: " . $e->getMessage());
        }
    }
}