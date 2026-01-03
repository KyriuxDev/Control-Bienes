<?php
// src/Application/UseCase/Resguardo/CreateResguardoUseCase.php
namespace App\Application\UseCase\Resguardo;

use App\Domain\Repository\ResguardoRepositoryInterface;
use App\Domain\Repository\TrabajadorRepositoryInterface;
use App\Domain\Repository\BienRepositoryInterface;
use App\Domain\Entity\Resguardo;
use App\Application\DTO\ResguardoDTO;

class CreateResguardoUseCase
{
    private $resguardoRepository;
    private $trabajadorRepository;
    private $bienRepository;

    public function __construct(
        ResguardoRepositoryInterface $resguardoRepository,
        TrabajadorRepositoryInterface $trabajadorRepository,
        BienRepositoryInterface $bienRepository
    ) {
        $this->resguardoRepository = $resguardoRepository;
        $this->trabajadorRepository = $trabajadorRepository;
        $this->bienRepository = $bienRepository;
    }

    public function execute(ResguardoDTO $dto)
    {
        // Validaciones
        if (empty($dto->trabajador_id)) {
            throw new \Exception("El trabajador es obligatorio");
        }

        if (empty($dto->bien_id)) {
            throw new \Exception("El bien es obligatorio");
        }

        if (empty($dto->fecha_asignacion)) {
            throw new \Exception("La fecha de asignación es obligatoria");
        }

        // Validar que el trabajador exista
        $trabajador = $this->trabajadorRepository->getById($dto->trabajador_id);
        if (!$trabajador) {
            throw new \Exception("Trabajador no encontrado");
        }

        // Validar que el bien exista
        $bien = $this->bienRepository->getById($dto->bien_id);
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        // Validar folio único si se proporciona
        if ($dto->folio) {
            $existente = $this->resguardoRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }

        // Crear la entidad
        $resguardo = new Resguardo();
        $resguardo->setFolio($dto->folio)
                  ->setTrabajadorId($dto->trabajador_id)
                  ->setBienId($dto->bien_id)
                  ->setFechaAsignacion($dto->fecha_asignacion)
                  ->setFechaDevolucion($dto->fecha_devolucion)
                  ->setLugar($dto->lugar)
                  ->setEstado(isset($dto->estado) ? $dto->estado : 'ACTIVO')
                  ->setNotasAdicionales($dto->notas_adicionales);

        // Guardar
        $this->resguardoRepository->begin();
        try {
            $this->resguardoRepository->persist($resguardo);
            $this->resguardoRepository->commit();
            
            // Retornar DTO con el ID generado
            $dto->id = $resguardo->getId();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear resguardo: " . $e->getMessage());
        }
    }
}
