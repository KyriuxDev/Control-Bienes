<?php
// src/Application/DTO/PrestamoDTO.php
namespace App\Application\DTO;

class PrestamoDTO
{
    public $id;
    public $folio;
    public $trabajador_id;
    public $fecha_emision;
    public $fecha_devolucion_programada;
    public $fecha_devolucion_real;
    public $lugar;
    public $matricula_autoriza;
    public $matricula_recibe;
    public $estado;
    public $observaciones;
    public $fecha_registro;

    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->folio = isset($data['folio']) ? $data['folio'] : null;
        $this->trabajador_id = isset($data['trabajador_id']) ? $data['trabajador_id'] : null;
        $this->fecha_emision = isset($data['fecha_emision']) ? $data['fecha_emision'] : null;
        $this->fecha_devolucion_programada = isset($data['fecha_devolucion_programada']) ? $data['fecha_devolucion_programada'] : null;
        $this->fecha_devolucion_real = isset($data['fecha_devolucion_real']) ? $data['fecha_devolucion_real'] : null;
        $this->lugar = isset($data['lugar']) ? $data['lugar'] : null;
        $this->matricula_autoriza = isset($data['matricula_autoriza']) ? $data['matricula_autoriza'] : null;
        $this->matricula_recibe = isset($data['matricula_recibe']) ? $data['matricula_recibe'] : null;
        $this->telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $this->fecha_registro = isset($data['fecha_registro']) ? $data['fecha_registro'] : null;
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'folio' => $this->folio,
            'trabajador_id' => $this->trabajador_id,
            'fecha_emision' => $this->fecha_emision,
            'fecha_devolucion_programada' => $this->fecha_devolucion_programada,
            'fecha_devolucion_real' => $this->fecha_devolucion_real,
            'lugar' => $this->lugar,
            'matricula_autoriza' => $this->matricula_autoriza,
            'matricula_recibe' => $this->matricula_recibe,
            'telefono' => $this->telefono,
            'fecha_registro' => $this->fecha_registro
        );
    }
}
