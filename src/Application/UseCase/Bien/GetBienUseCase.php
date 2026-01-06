<?php
// src/Application/UseCase/Bien/GetBienUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Application\DTO\BienDTO;

class GetBienUseCase
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

        return new BienDTO([
            'id' => $bien->getId(),
            'identificacion' => $bien->getIdentificacion(),
            'descripcion' => $bien->getDescripcion(),
            'marca' => $bien->getMarca(),
            'modelo' => $bien->getModelo(),
            'serie' => $bien->getSerie(),
            'naturaleza' => $bien->getNaturaleza(),
            'estado_fisico' => $bien->getEstadoFisico(),
            'fecha_registro' => $bien->getFechaRegistro()
        ]);
    }

    public function executeByIdentificacion($identificacion)
    {
        $bien = $this->bienRepository->findByIdentificacion($identificacion);
        
        if (!$bien) {
            throw new \Exception("Bien con identificación {$identificacion} no encontrado");
        }

        return new BienDTO([
            'id' => $bien->getId(),
            'identificacion' => $bien->getIdentificacion(),
            'descripcion' => $bien->getDescripcion(),
            'marca' => $bien->getMarca(),
            'modelo' => $bien->getModelo(),
            'serie' => $bien->getSerie(),
            'naturaleza' => $bien->getNaturaleza(),
            'estado_fisico' => $bien->getEstadoFisico(),
            'fecha_registro' => $bien->getFechaRegistro()
        ]);
    }
}
