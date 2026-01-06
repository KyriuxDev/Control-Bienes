<?php
// src/Domain/Entity/Bien.php
namespace App\Domain\Entity;

class Bien
{

    public $id_bien;
    public $naturaleza;
    public $marca;
    public $modelo;
    public $serie;
    public $descripcion;

    public function getIdBien() {
        return $this->id_bien;
    }

    public function setIdBien($id_bien) {
        $this->id_bien = $id_bien;
        return $this;
    }

    public function getNaturaleza() {
        return $this->naturaleza;
    }

    public function setNaturaleza($naturaleza) {
        $this->naturaleza = $naturaleza;
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

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
        return $this;
    }

}