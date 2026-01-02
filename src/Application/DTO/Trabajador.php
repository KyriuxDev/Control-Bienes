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

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->cargo = $data['cargo'] ?? null;
        $this->institucion = $data['institucion'] ?? null;
        $this->adscripcion = $data['adscripcion'] ?? null;
        $this->matricula = $data['matricula'] ?? null;
        $this->identificacion = $data['identificacion'] ?? null;
        $this->direccion = $data['direccion'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->fecha_registro = $data['fecha_registro'] ?? null;
    }

    public function toArray()
    {
        return [
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
        ];
    }
}