<?php
// src/Application/DTO/TrabajadorDTO.php
namespace App\Application\DTO;

class TrabajadorDTO
{
    public $id;
    public $nombre;
    public $cargo;
    public $institucion;
    public $adscripcion;
    public $matricula;
    public $identificacion;
    public $direccion;
    public $telefono;
    public $fecha_registro;

    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $this->cargo = isset($data['cargo']) ? $data['cargo'] : null;
        $this->institucion = isset($data['institucion']) ? $data['institucion'] : null;
        $this->adscripcion = isset($data['adscripcion']) ? $data['adscripcion'] : null;
        $this->matricula = isset($data['matricula']) ? $data['matricula'] : null;
        $this->identificacion = isset($data['identificacion']) ? $data['identificacion'] : null;
        $this->direccion = isset($data['direccion']) ? $data['direccion'] : null;
        $this->telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $this->fecha_registro = isset($data['fecha_registro']) ? $data['fecha_registro'] : null;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'nombre' => $this->nombre,
            'cargo' => $this->cargo,
            'institucion' => $this->institucion,
            'adscripcion' => $this->adscripcion,
            'matricula' => $this->matricula,
            'identificacion' => $this->identificacion,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'fecha_registro' => $this->fecha_registro
        );
    }
}