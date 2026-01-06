<?php
// src/Domain/Entity/Trabajador.php

namespace App\Domain\Entity;

class Trabajador
{
    public $matricula;
    public $nombre;
    public $institucion; //Institucion o Dependencia
    public $adscripcion; //Adscripcion o Dirección
    public $identificacion;
    public $telefono;
    public $cargo;

    public function getMatricula() {
        return $this->matricula;
    }

    public function setMatricula($matricula) {
        $this->matricula = $matricula;
        return $this;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
        return $this;
    }

    public function getInstitucion() {
        return $this->institucion;
    }

    public function setInstitucion($institucion) {
        $this->institucion = $institucion;
        return $this;
    }

    public function getAdscripcion() {
        return $this->adscripcion;
    }

    public function setAdscripcion($adscripcion) {
        $this->adscripcion = $adscripcion;
        return $this;
    }

    public function getIdentificacion() {
        return $this->identificacion;
    }

    public function setIdentificacion($identificacion) {
        $this->identificacion = $identificacion;
        return $this;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
        return $this;
    }

    public function getCargo() {
        return $this->cargo;
    }

    public function setCargo($cargo) {
        $this->cargo = $cargo;
        return $this;
    }
    
}

