<?php
// src/Domain/Entity/Trabajador.php

namespace App\Domain\Entity;

class Trabajador extends AbstractEntity
{
    protected $nombre;
    protected $cargo;
    protected $institucion;
    protected $adscripcion;
    protected $matricula;
    protected $identificacion;
    protected $direccion;
    protected $telefono;
    protected $fecha_registro;

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
        return $this;
    }

    public function getCargo() {
        return $this->cargo;
    }

    public function setCargo($cargo) {
        $this->cargo = $cargo;
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

    public function getMatricula() {
        return $this->matricula;
    }

    public function setMatricula($matricula) {
        $this->matricula = $matricula;
        return $this;
    }

    public function getIdentificacion() {
        return $this->identificacion;
    }

    public function setIdentificacion($identificacion) {
        $this->identificacion = $identificacion;
        return $this;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
        return $this;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
        return $this;
    }

    public function getFechaRegistro() {
        return $this->fecha_registro;
    }

    public function setFechaRegistro($fecha_registro) {
        $this->fecha_registro= $fecha_registro;
        return $this;
    }


    
}

