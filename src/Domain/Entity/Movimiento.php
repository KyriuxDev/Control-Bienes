<?php
// src/Domain/Entity/Movimiento.php

namespace App\Domain\Entity;

class Movimiento{
    public $id_movimiento;
    public $tipo_movimiento;
    public $matricula_recibe;
    public $matricula_entrega;
    public $fecha;
    public $lugar;
    public $area;
    public $folio;
    public $dias_prestamo;

    public function getIdMovimiento(){
        return $this->id_movimiento;
    }

    public function setIdMovimiento($id_movimiento){
        $this->id_movimiento = $id_movimiento;
        return $this;
    }

    public function getTipoMovimiento(){
        return $this->tipo_movimiento;
    }

    public function setTipoMovimiento($tipo_movimiento){
        $this->tipo_movimiento = $tipo_movimiento;
        return $this;
    }

    public function getMatriculaRecibe() {
        return $this->matricula_recibe;
    }

    public function setMatriculaRecibe($matricula_recibe) {
        $this->matricula_recibe = $matricula_recibe;
        return $this;
    }

    public function getMatriculaEntrega() {
        return $this->matricula_entrega;
    }

    public function setMatriculaEntrega($matricula_entrega) {
        $this->matricula_entrega = $matricula_entrega;
        return $this;
    }

    public function getFecha(){
        return $this->fecha;
    }

    public function setFecha($fecha){
        $this->fecha = $fecha;
        return $this;
    }

    public function getLugar(){
        return $this->lugar;
    }

    public function setLugar($lugar){
        $this->lugar = $lugar;
        return $this;
    }

    public function getArea(){
        return $this->area;
    }

    public function setArea($area){
        $this->area = $area;
        return $this;
    }

    public function getFolio(){
        return $this->folio;
    }

    public function setFolio($folio){
        $this->folio = $folio;
        return $this;
    }

    public function getDiasPrestamo(){
        return $this->dias_prestamo;
    }

    public function setDiasPrestamo($dias_prestamo){
        $this->dias_prestamo = $dias_prestamo;
        return $this;
    }
}