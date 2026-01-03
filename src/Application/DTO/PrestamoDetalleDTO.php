<?php

namespace App\Application\DTO;

class PrestamoDetalleDTO
{
    public $id;
    public $prestamo_id;
    public $bien_id;
    public $cantidad;

    public function __construct( array $data = array() ){
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->prestamo_id = isset($data['prestamo_id']) ? $data['prestamo_id']:null;
        $this->bien_id = isset($data['bien_id']) ? $data['bien_id']:null;
        $this->cantidad = isset($data['cantidad']) ? $data['cantidad']:null;
        
    }
    
    public function toArray(){
        return array(
            'id'=>$this->'id',
            'prestamo_id' =>$this->'prestamo_id',
            'bien_id' =>$this->'bien_id',
            'cantidad' => $this->'cantidad'
        );
    }
}