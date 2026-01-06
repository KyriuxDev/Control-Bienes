<?php
// src/Application/UseCase/SalidaBien/CreateSalidaBienUseCase.php
namespace App\Application\UseCase\SalidaBien;

use App\Domain\Repository\SalidaBienRepositoryInterface;
use App\Domain\Repository\SalidaDetalleRepositoryInterface;
use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Entity\SalidaBien;
use App\Domain\Entity\SalidaDetalle;
use App\Application\DTO\SalidaBienDTO;

class CreateSalidaBienUseCase
{
    private $salidaBienRepository;
    private $salidaDetalleRepository;
    private $trabajadorRepository;

    public function __construct(
        SalidaBienRepositoryInterface $salidaBienRepository,
        SalidaDetalleRepositoryInterface $salidaDetalleRepository,
        TrabajadorRepositoryInterface $trabajadorRepository
    ) {
        $this->salidaBienRepository = $salidaBienRepository;
        $this->salidaDetalleRepository = $salidaDetalleRepository;
        $this->trabajadorRepository = $trabajadorRepository;
    }

    public function execute(SalidaBienDTO $dto, array $bienes = [])
    {
        // Validaciones
        if (empty($dto->trabajador_id)) {
            throw new \Exception("El trabajador es obligatorio");
        }

        if (empty($dto->fecha_salida)) {
            throw new \Exception("La fecha de salida es obligatoria");
        }

        if (empty($dto->destino)) {
            throw new \Exception("El destino es obligatorio");
        }

        // Validar que el trabajador exista
        $trabajador = $this->trabajadorRepository->getById($dto->trabajador_id);
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Validar que haya bienes en la salida
        if (empty($bienes)) {
            throw new \Exception("Debe incluir al menos un bien en la salida");
        }

        // Validar folio único si se proporciona
        if ($dto->folio) {
            $existente = $this->salidaBienRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }

        // Crear la entidad
        $salidaBien = new SalidaBien();
        $salidaBien->setFolio($dto->folio)
                   ->setTrabajadorId($dto->trabajador_id)
                   ->setAreaOrigen($dto->area_origen)
                   ->setDestino($dto->destino)
                   ->setFechaSalida($dto->fecha_salida)
                   ->setFechaDevolucionProgramada($dto->fecha_devolucion_programada)
                   ->setSujetoDevolucion(isset($dto->sujeto_devolucion) ? $dto->sujeto_devolucion : true)
                   ->setLugar($dto->lugar)
                   ->setObservacionesEstado($dto->observaciones_estado)
                   ->setEstado(isset($dto->estado) ? $dto->estado : 'AUTORIZADO'); 

        // Guardar
        $this->salidaBienRepository->begin();
        try {
            $this->salidaBienRepository->persist($salidaBien);
            
            // Guardar los detalles de la salida
            foreach ($bienes as $bienData) {
                $detalle = new SalidaDetalle();
                $detalle->setSalidaId($salidaBien->getId())
                       ->setBienId($bienData['bien_id'])
                       ->setCantidad(isset($bienData['cantidad']) ? $bienData['cantidad'] : 1); 
                
                $this->salidaDetalleRepository->persist($detalle);
            }
            
            $this->salidaBienRepository->commit();
            
            // Retornar DTO con el ID generado
            $dto->id = $salidaBien->getId();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear salida de bien: " . $e->getMessage());
        }
    }
}
