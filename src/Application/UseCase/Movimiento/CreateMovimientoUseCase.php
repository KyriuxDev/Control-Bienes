<?php
// src/Application/UseCase/Movimiento/CreateMovimientoUseCase.php
namespace App\Application\UseCase\Movimiento;

use App\Domain\Repository\MovimientoRepositoryInterface;
use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\Movimiento;
use App\Application\DTO\MovimientoDTO;

class CreateMovimientoUseCase
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
        // Validar que el tipo de movimiento sea válido
        $tiposValidos = ['Prestamo', 'Resguardo', 'Transferencia', 'Devolucion'];
        if (!in_array($dto->tipo_movimiento, $tiposValidos)) {
            throw new \Exception("Tipo de movimiento inválido. Debe ser: Prestamo, Resguardo, Transferencia o Devolucion");
        }

        // Validar que el trabajador que recibe exista
        if ($dto->matricula_recibe) {
            $trabajadorRecibe = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula_recibe);
            if (!$trabajadorRecibe) {
                throw new \Exception("El trabajador con matrícula {$dto->matricula_recibe} no existe");
            }
        }

        // Validar que el trabajador que entrega exista
        if ($dto->matricula_entrega) {
            $trabajadorEntrega = $this->trabajadorRepository->obtenerPorMatricula($dto->matricula_entrega);
            if (!$trabajadorEntrega) {
                throw new \Exception("El trabajador con matrícula {$dto->matricula_entrega} no existe");
            }
        }

        // Validar fecha (no puede ser futura)
        if ($dto->fecha && strtotime($dto->fecha) > time()) {
            throw new \Exception("La fecha del movimiento no puede ser futura");
        }

        // Crear la entidad
        $movimiento = new Movimiento();
        $movimiento->setTipoMovimiento($dto->tipo_movimiento)
                   ->setMatriculaRecibe($dto->matricula_recibe)
                   ->setMatriculaEntrega($dto->matricula_entrega)
                   ->setFecha($dto->fecha ?: date('Y-m-d H:i:s'))
                   ->setLugar($dto->lugar)
                   ->setArea($dto->area)
                   ->setFolio($dto->folio)
                   ->setDiasPrestamo($dto->dias_prestamo);

        // Guardar
        try {
            $this->movimientoRepository->persist($movimiento);
            
            // Retornar DTO con el ID generado
            $dto->id_movimiento = $movimiento->getIdMovimiento();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear movimiento: " . $e->getMessage());
        }
    }
}