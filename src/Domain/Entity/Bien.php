<?php
// src/Domain/Entity/Bien.php
namespace App\Domain\Entity;

class Bien extends AbstractEntity
{
    protected $identificacion;
    protected $descripcion;
    protected $marca;
    protected $modelo;
    protected $serie;
    protected $naturaleza;
    protected $estado_fisico;
    protected $fecha_registro;

    public function getIdentificacion() {
        return $this->identificacion;
    }

    public function setIdentificacion($identificacion) {
        $this->identificacion = $identificacion;
        return $this;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getMarca() {
        return $this->marca;
    }

    public function setMarca($marca) {
        $this->marca = $marca;
        return $this;
    }

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
        return $this;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function setSerie($serie) {
        $this->serie = $serie;
        return $this;
    }

    public function getNaturaleza() {
        return $this->naturaleza;
    }

    public function setNaturaleza($naturaleza) {
        $this->naturaleza = $naturaleza;
        return $this;
    }

    public function getEstadoFisico() {
        return $this->estado_fisico;
    }

    public function setEstadoFisico($estado_fisico) {
        $this->estado_fisico = $estado_fisico;
        return $this;
    }

    public function getFechaRegistro() {
        return $this->fecha_registro;
    }

    public function setFechaRegistro($fecha_registro) {
        $this->fecha_registro = $fecha_registro;
        return $this;
    }
}