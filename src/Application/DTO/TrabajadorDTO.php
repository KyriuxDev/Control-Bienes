<?php
// src/Application/DTO/TrabajadorDTO.php
namespace App\Application\DTO;

class TrabajadorDTO
{
    public $matricula;
    public $nombre;
    public $cargo;
    public $institucion;
    public $adscripcion;
    public $identificacion;
    public $telefono;

    public function __construct(array $data = array())
    {
        // Usar matricula para coincidir con la entidad del dominio (no id)
        $this->matricula = isset($data['matricula']) ? $data['matricula'] : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $this->cargo = isset($data['cargo']) ? $data['cargo'] : null;
        $this->institucion = isset($data['institucion']) ? $data['institucion'] : null;
        $this->adscripcion = isset($data['adscripcion']) ? $data['adscripcion'] : null;
        $this->identificacion = isset($data['identificacion']) ? $data['identificacion'] : null;
        $this->telefono = isset($data['telefono']) ? $data['telefono'] : null;
    }

    public function toArray()
    {
        return array(
            'matricula' => $this->matricula,
            'nombre' => $this->nombre,
            'cargo' => $this->cargo,
            'institucion' => $this->institucion,
            'adscripcion' => $this->adscripcion,
            'identificacion' => $this->identificacion,
            'telefono' => $this->telefono
        );
    }
}