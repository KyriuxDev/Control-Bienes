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

    public function execute($id_bien)
    {
        $bien = $this->bienRepository->obtenerPorId($id_bien);
        
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        return new BienDTO([
            'id_bien' => $bien->getIdBien(),
            'naturaleza' => $bien->getNaturaleza(),
            'marca' => $bien->getMarca(),
            'modelo' => $bien->getModelo(),
            'serie' => $bien->getSerie(),
            'descripcion' => $bien->getDescripcion()
        ]);
    }
}