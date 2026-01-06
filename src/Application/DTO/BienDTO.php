<?php
// src/Application/DTO/BienDTO.php
namespace App\Application\DTO;

class BienDTO
{
    public $id_bien;
    public $naturaleza;
    public $marca;
    public $modelo;
    public $serie;
    public $descripcion;

    public function __construct(array $data = array())
    {
        $this->id_bien = isset($data['id_bien']) ? $data['id_bien'] : null;
        $this->naturaleza = isset($data['naturaleza']) ? $data['naturaleza'] : null;
        $this->marca = isset($data['marca']) ? $data['marca'] : null;
        $this->modelo = isset($data['modelo']) ? $data['modelo'] : null;
        $this->serie = isset($data['serie']) ? $data['serie'] : null;
        $this->descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
    }

    public function toArray()
    {
        return array(
            'id_bien' => $this->id_bien,
            'naturaleza' => $this->naturaleza,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'descripcion' => $this->descripcion
        );
    }
}