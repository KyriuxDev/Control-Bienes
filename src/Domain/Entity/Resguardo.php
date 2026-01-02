<?php
// src/Domain/Entity/Resguardo.php
namespace App\Domain\Entity;

class Resguardo extends AbstractEntity
{
    protected $folio;
    protected $empleado_id;
    protected $bien_id;
    protected $fecha_asignacion;
    protected $fecha_devolucion;
    protected $lugar;
    protected $estado;
    protected $notas_adicionales;
    protected $fecha_registro;

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
        return $this;
    }

    public function getEmpleadoId() {
        return $this->empleado_id;
    }

    public function setEmpleadoId($empleado_id) {
        $this->empleado_id = $empleado_id;
        return $this;
    }

    public function getBienId() {
        return $this->bien_id;
    }

    public function setBienId($bien_id) {
        $this->bien_id = $bien_id;
        return $this;
    }

    public function getFechaAsignacion() {
        return $this->fecha_asignacion;
    }

    public function setFechaAsignacion($fecha_asignacion) {
        $this->fecha_asignacion = $fecha_asignacion;
        return $this;
    }

    public function getFechaDevolucion() {
        return $this->fecha_devolucion;
    }

    public function setFechaDevolucion($fecha_devolucion) {
        $this->fecha_devolucion = $fecha_devolucion;
        return $this;
    }

    public function getLugar() {
        return $this->lugar;
    }

    public function setLugar($lugar) {
        $this->lugar = $lugar;
        return $this;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
        return $this;
    }

    public function getNotasAdicionales() {
        return $this->notas_adicionales;
    }

    public function setNotasAdicionales($notas_adicionales) {
        $this->notas_adicionales = $notas_adicionales;
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