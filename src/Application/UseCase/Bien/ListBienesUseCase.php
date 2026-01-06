<?php
// src/Application/UseCase/Bien/ListBienesUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Application\DTO\BienDTO;

class ListBienesUseCase
{
    private $bienRepository;

    public function __construct(BienRepositoryInterface $bienRepository)
    {
        $this->bienRepository = $bienRepository;
    }

    public function execute()
    {
        $bienes = $this->bienRepository->obtenerTodos();
        
        $bienesDTO = [];
        foreach ($bienes as $bien) {
            $bienesDTO[] = new BienDTO([
                'id_bien' => $bien->getIdBien(),
                'naturaleza' => $bien->getNaturaleza(),
                'marca' => $bien->getMarca(),
                'modelo' => $bien->getModelo(),
                'serie' => $bien->getSerie(),
                'descripcion' => $bien->getDescripcion()
            ]);
        }

        return $bienesDTO;
    }

    public function executeByNaturaleza($naturaleza)
    {
        // Validar que la naturaleza sea válida
        $naturalezasValidas = ['BC', 'BMNC', 'BMC', 'BPS'];
        if (!in_array($naturaleza, $naturalezasValidas)) {
            throw new \Exception("Naturaleza inválida. Debe ser: BC, BMNC, BMC o BPS");
        }

        $bienes = $this->bienRepository->buscarPorNaturaleza($naturaleza);
        
        $bienesDTO = [];
        foreach ($bienes as $bien) {
            $bienesDTO[] = new BienDTO([
                'id_bien' => $bien->getIdBien(),
                'naturaleza' => $bien->getNaturaleza(),
                'marca' => $bien->getMarca(),
                'modelo' => $bien->getModelo(),
                'serie' => $bien->getSerie(),
                'descripcion' => $bien->getDescripcion()
            ]);
        }

        return $bienesDTO;
    }
}