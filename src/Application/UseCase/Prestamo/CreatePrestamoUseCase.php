<?php
// src/Application/UseCase/Prestamo/CreatePrestamoUseCase.php
namespace App\Application\UseCase\Prestamo;

use App\Domain\Repository\PrestamoRepositoryInterface;
use App\Domain\Repository\PrestamoDetalleRepositoryInterface;
use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\Prestamo;
use App\Domain\Entity\PrestamoDetalle;
use App\Application\DTO\PrestamoDTO;

class CreatePrestamoUseCase
{
    private $prestamoRepository;
    private $prestamoDetalleRepository;
    private $trabajadorRepository;

    public function __construct(
        PrestamoRepositoryInterface $prestamoRepository,
        PrestamoDetalleRepositoryInterface $prestamoDetalleRepository,
        TrabajadorRepositoryInterface $trabajadorRepository
    ) {
        $this->prestamoRepository = $prestamoRepository;
        $this->prestamoDetalleRepository = $prestamoDetalleRepository;
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute(PrestamoDTO $dto, array $bienes = [])
    {
        // Validaciones
        if (empty($dto->trabajador_id)) {
            throw new \Exception("El trabajador es obligatorio");
        }

        if (empty($dto->fecha_emision)) {
            throw new \Exception("La fecha de emisión es obligatoria");
        }

        if (empty($dto->fecha_devolucion_programada)) {
            throw new \Exception("La fecha de devolución programada es obligatoria");
        }

        // Validar que el trabajador exista
        $trabajador = $this->trabajadorRepository->getById($dto->trabajador_id);
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Validar que haya bienes en el préstamo
        if (empty($bienes)) {
            throw new \Exception("Debe incluir al menos un bien en el préstamo");
        }

        // Validar folio único si se proporciona
        if ($dto->folio) {
            $existente = $this->prestamoRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }
        
        $estado = (isset($dto->estado) && !empty($dto->estado)) ? $dto->estado : 'ACTIVO';

        // Crear la entidad
        $prestamo = new Prestamo();
        $prestamo->setFolio($dto->folio)
                 ->setTrabajadorId($dto->trabajador_id)
                 ->setFechaEmision($dto->fecha_emision)
                 ->setFechaDevolucionProgramada($dto->fecha_devolucion_programada)
                 ->setFechaDevolucionReal($dto->fecha_devolucion_real)
                 ->setLugar($dto->lugar)
                 ->setMatriculaAutoriza($dto->matricula_autoriza)
                 ->setMatriculaRecibe($dto->matricula_recibe)
                 ->setEstado($estado) 
                 ->setObservaciones($dto->observaciones);

        // Guardar
        $this->prestamoRepository->begin();
        try {
            $this->prestamoRepository->persist($prestamo);
            
            // Guardar los detalles del préstamo
            foreach ($bienes as $bienData) {
                $detalle = new PrestamoDetalle();
                $detalle->setPrestamoId($prestamo->getId())
                       ->setBienId($bienData['bien_id'])
                       ->setCantidad(isset($dto->cantidad) ? $dto->cantidad : 1);
                $this->prestamoDetalleRepository->persist($detalle);
            }
            
            $this->prestamoRepository->commit();
            
            // Retornar DTO con el ID generado
            $dto->id = $prestamo->getId();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear préstamo: " . $e->getMessage());
        }
    }
}
