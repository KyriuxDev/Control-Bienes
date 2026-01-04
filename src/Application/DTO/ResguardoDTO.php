<?php

namespace App\Application\DTO;

class ResguardoDTO
{
    public $id;
    public $folio;
    public $trabajador_id;
    public $bien_id;
    public $fecha_asignacion;
    public $fecha_devolucion;
    public $lugar;
    public $estado;
    public $notas_adicionales;
    public $fecha_registro; 
    
    public function __construct(array $data = array()){
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->folio = isset($data['folio']) ? $data['folio'] : null;
        $this->trabajador_id = isset($data['trabajador_id']) ? $data['trabajador_id'] : null;
        $this->bien_id = isset($data['bien_id']) ? $data['bien_id'] : null;
        $this->fecha_asignacion = isset($data['fecha_asignacion']) ? $data['fecha_asignacion'] : null;
        $this->fecha_devolucion = isset($data['fecha_devolucion']) ? $data['fecha_devolucion'] : null;
        $this->lugar = isset($data['lugar']) ? $data['lugar'] : null;
        $this->estado = isset($data['estado']) ? $data['estado'] : null;
        $this->notas_adicionales = isset($data['notas_adicionales']) ? $data['notas_adicionales'] : null;
        $this->fecha_registro = isset($data['fecha_registro']) ? $data['fecha_registro'] : null;
        
    }
    
    public function toArray(){
        return array(
            'id' => $this->id,
            'folio' => $this->folio,
            'trabajador_id' => $this->trabajador_id,
            'bien_id' => $this->bien_id,
            'fecha_asignacion' => $this->fecha_asignacion,
            'fecha_devolucion' => $this->fecha_devolucion,
            'lugar' => $this->lugar,
            'estado' => $this->estado,
            'notas_adicionales' => $this->notas_adicionales,
            'fecha_registro' => $this->fecha_registro
        );
    }
}