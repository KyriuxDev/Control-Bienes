<?php
// src/Application/DTO/DetalleMovimientoDTO.php
namespace App\Application\DTO;

class DetalleMovimientoDTO
{
    public $id_movimiento;
    public $id_bien;
    public $cantidad;
    public $estado_fisico;
    public $sujeto_devolucion;

    public function __construct(array $data = array())
    {
        $this->id_movimiento = isset($data['id_movimiento']) ? $data['id_movimiento'] : null;
        $this->id_bien = isset($data['id_bien']) ? $data['id_bien'] : null;
        $this->cantidad = isset($data['cantidad']) ? $data['cantidad'] : null;
        $this->estado_fisico = isset($data['estado_fisico']) ? $data['estado_fisico'] : null;
        $this->sujeto_devolucion = isset($data['sujeto_devolucion']) ? $data['sujeto_devolucion'] : null;
    }

    public function toArray()
    {
        return array(
            'id_movimiento' => $this->id_movimiento,
            'id_bien' => $this->id_bien,
            'cantidad' => $this->cantidad,
            'estado_fisico' => $this->estado_fisico,
            'sujeto_devolucion' => $this->sujeto_devolucion
        );
    }
}