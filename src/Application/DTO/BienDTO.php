<?php
// src/Application/DTO/BienDTO.php
namespace App\Application\DTO;

class BienDTO
{
    public $id;
    public $identificacion;
    public $descripcion;
    public $marca;
    public $modelo;
    public $serie;
    public $naturaleza;
    public $estado_fisico;
    public $fecha_registro;

    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->identificacion = isset($data['identificacion']) ? $data['identificacion'] : null;
        $this->descripcion = isset($data['descripcion']) ? $data['descripcion'] : null;
        $this->marca = isset($data['marca']) ? $data['marca'] : null;
        $this->modelo = isset($data['modelo']) ? $data['modelo'] : null;
        $this->serie = isset($data['serie']) ? $data['serie'] : null;
        $this->naturaleza = isset($data['naturaleza']) ? $data['naturaleza'] : null;
        $this->estado_fisico = isset($data['estado_fisico']) ? $data['estado_fisico'] : null;
        $this->fecha_registro = isset($data['fecha_registro']) ? $data['fecha_registro'] : null;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'identificacion' => $this->identificacion,
            'descripcion' => $this->descripcion,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'naturaleza' => $this->naturaleza,
            'estado_fisico' => $this->estado_fisico,
            'fecha_registro' => $this->fecha_registro
        );
    }
}