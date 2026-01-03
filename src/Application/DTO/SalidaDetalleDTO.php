<?php

namespace App\Application\DTO;

class SalidaDetalleDTO
{
    public $id;
    public $salida_id;
    public $bien_id;
    public $cantidad;

    public function __construct( array $data = array() ){
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->salida_id = isset($data['salida_id']) ? $data['salida_id']:null;
        $this->bien_id = isset($data['bien_id']) ? $data['bien_id']:null;
        $this->cantidad = isset($data['cantidad']) ? $data['cantidad']:null;
        
    }
    
    public function toArray(){
        return array(
            'id'=>$this->'id',
            'salida_id' =>$this->'salida_id',
            'bien_id' =>$this->'bien_id',
            'cantidad' => $this->'cantidad'
        );
    }
}
