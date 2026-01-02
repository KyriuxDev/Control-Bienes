<?php
// src/Domain/Entity/Prestamo.php
namespace App\Domain\Entity;

class Prestamo extends AbstractEntity
{
    protected $folio;
    protected $empleado_id;
    protected $fecha_emision;
    protected $fecha_devolucion_programada;
    protected $fecha_devolucion_real;
    protected $lugar;
    protected $matricula_autoriza;
    protected $matricula_recibe;
    protected $estado;
    protected $observaciones;
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

    public function getFechaEmision() {
        return $this->fecha_emision;
    }

    public function setFechaEmision($fecha_emision) {
        $this->fecha_emision = $fecha_emision;
        return $this;
    }

    public function getFechaDevolucionProgramada() {
        return $this->fecha_devolucion_programada;
    }

    public function setFechaDevolucionProgramada($fecha_devolucion_programada) {
        $this->fecha_devolucion_programada = $fecha_devolucion_programada;
        return $this;
    }

    public function getFechaDevolucionReal() {
        return $this->fecha_devolucion_real;
    }

    public function setFechaDevolucionReal($fecha_devolucion_real) {
        $this->fecha_devolucion_real = $fecha_devolucion_real;
        return $this;
    }

    public function getLugar() {
        return $this->lugar;
    }

    public function setLugar($lugar) {
        $this->lugar = $lugar;
        return $this;
    }

    public function getMatriculaAutoriza() {
        return $this->matricula_autoriza;
    }

    public function setMatriculaAutoriza($matricula_autoriza) {
        $this->matricula_autoriza = $matricula_autoriza;
        return $this;
    }

    public function getMatriculaRecibe() {
        return $this->matricula_recibe;
    }

    public function setMatriculaRecibe($matricula_recibe) {
        $this->matricula_recibe = $matricula_recibe;
        return $this;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
        return $this;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
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