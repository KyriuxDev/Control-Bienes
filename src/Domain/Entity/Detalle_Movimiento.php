<?php
// src/Domain/Entity/Detalle_Movimiento.php

namespace App\Domain\Entity;

class Detalle_Movimiento{
    public $id_movimiento;
    public $id_bien;
    public $cantidad;
    public $estado_fisico;
    public $sujeto_devolucion;


    public function getIdMovimiento(){
        return $this->id_movimiento;
    }

    public function setIdMovimiento($id_moviento){
        $this->id_moviento = $id_movimiento;
        return $this;
    }

    public function getIdBien() {
        return $this->id_bien;
    }

    public function setIdBien($id_bien) {
        $this->id_bien = $id_bien;
        return $this;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
        return $this;
    }

    public function getEstadoFisico() {
        return $this->estado_fisico;
    }

    public function setEstadoFisico($estado_fisico) {
        $this->estado_fisico = $estado_fisico;
        return $this;
    }

    public function getSujetoDevolucion() {
        return $this->sujeto_devolucion;
    }

    public function setSujetoDevolucion($sujeto_devolucion) {
        $this->sujeto_devolucion = $sujeto_devolucion;
        return $this;
    }
}