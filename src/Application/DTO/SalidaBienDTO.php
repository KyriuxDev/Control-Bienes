<?php

namespace App\Domain\Entity;

class SalidaBienDTO
{
    public $id;
    public $folio;
    public $trabajador_id;
    public $area_origen;
    public $destino;
    public $fecha_salida;
    public $fecha_devolucion_programada;
    public $sujeto_devolucion;
    public $lugar;
    public $observaciones_estado;
    public $estado;


    public function __construct(array $data = array())
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->folio = isset($data['folio']) ? $data['folio'] : null;
        $this->trabajador_id = isset($data['trabajador_id']) ? $data['trabajador_id'] : null;
        $this->area_origen = isset($data['area_origen']) ? $data['area_origen'] : null;
        $this->destino = isset($data['destino']) ? $data['destino'] : null;
        $this->fecha_salida = isset($data['fecha_salida']) ? $data['fecha_salida'] : null;
        $this->fecha_devolucion_programada = isset($data['fecha_devolucion_programada']) ? $data['fecha_devolucion_programada'] : null;
        $this->sujeto_devolucion = isset($data['sujeto_devolucion']) ? $data['sujeto_devolucion'] : null;
        $this->lugar = isset($data['lugar']) ? $data['lugar'] : null;
        $this->observaciones_estado = isset($data['observaciones_estado']) ? $data['observaciones_estado'] : null;
        $this->estado = isset($data['estado']) ? $data['estado'] : null;

    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'folio' => $this->folio,
            'trabajador_id' => $this->trabajador_id,
            'area_origen' => $this->area_origen,
            'destino' => $this->destino,
            'fecha_salida' => $this->fecha_salida,
            'fecha_devolucion_programada' => $this->fecha_devolucion_programada,
            'sujeto_devolucion' => $this->sujeto_devolucion,
            'lugar' => $this->lugar,
            'observaciones_estado' => $this->observaciones_estado
            'estado' => $this->estado,

        );
    }
}
