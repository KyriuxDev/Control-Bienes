<?php
// src/Application/DTO/MovimientoDTO.php
namespace App\Application\DTO;

class MovimientoDTO
{
    public $id_movimiento;
    public $tipo_movimiento;
    public $matricula_recibe;
    public $matricula_entrega;
    public $fecha;
    public $lugar;
    public $area;
    public $folio;
    public $dias_prestamo;

    public function __construct(array $data = array())
    {
        $this->id_movimiento = isset($data['id_movimiento']) ? $data['id_movimiento'] : null;
        $this->tipo_movimiento = isset($data['tipo_movimiento']) ? $data['tipo_movimiento'] : null;
        $this->matricula_recibe = isset($data['matricula_recibe']) ? $data['matricula_recibe'] : null;
        $this->matricula_entrega = isset($data['matricula_entrega']) ? $data['matricula_entrega'] : null;
        $this->fecha = isset($data['fecha']) ? $data['fecha'] : null;
        $this->lugar = isset($data['lugar']) ? $data['lugar'] : null;
        $this->area = isset($data['area']) ? $data['area'] : null;
        $this->folio = isset($data['folio']) ? $data['folio'] : null;
        $this->dias_prestamo = isset($data['dias_prestamo']) ? $data['dias_prestamo'] : null;
    }

    public function toArray()
    {
        return array(
            'id_movimiento' => $this->id_movimiento,
            'tipo_movimiento' => $this->tipo_movimiento,
            'matricula_recibe' => $this->matricula_recibe,
            'matricula_entrega' => $this->matricula_entrega,
            'fecha' => $this->fecha,
            'lugar' => $this->lugar,
            'area' => $this->area,
            'folio' => $this->folio,
            'dias_prestamo' => $this->dias_prestamo
        );
    }
}