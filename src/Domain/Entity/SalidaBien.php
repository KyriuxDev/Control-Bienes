<?php
// src/Domain/Entity/SalidaBien.php
namespace App\Domain\Entity;

class SalidaBien extends AbstractEntity
{
    protected $folio;
    protected $trabajador_id;
    protected $area_origen;
    protected $destino;
    protected $fecha_salida;
    protected $fecha_devolucion_programada;
    protected $sujeto_devolucion;
    protected $lugar;
    protected $observaciones_estado;
    protected $estado;
    protected $fecha_registro;

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
        return $this;
    }

    public function getTrabajadorId() {
        return $this->trabajador_id;
    }

    public function setTrabajadorId($trabajador_id) {
        $this->trabajador_id = $trabajador_id;
        return $this;
    }

    public function getAreaOrigen() {
        return $this->area_origen;
    }

    public function setAreaOrigen($area_origen) {
        $this->area_origen = $area_origen;
        return $this;
    }

    public function getDestino() {
        return $this->destino;
    }

    public function setDestino($destino) {
        $this->destino = $destino;
        return $this;
    }

    public function getFechaSalida() {
        return $this->fecha_salida;
    }

    public function setFechaSalida($fecha_salida) {
        $this->fecha_salida = $fecha_salida;
        return $this;
    }

    public function getFechaDevolucionProgramada() {
        return $this->fecha_devolucion_programada;
    }

    public function setFechaDevolucionProgramada($fecha_devolucion_programada) {
        $this->fecha_devolucion_programada = $fecha_devolucion_programada;
        return $this;
    }

    public function getSujetoDevolucion() {
        return $this->sujeto_devolucion;
    }

    public function setSujetoDevolucion($sujeto_devolucion) {
        $this->sujeto_devolucion = $sujeto_devolucion;
        return $this;
    }

    public function getLugar() {
        return $this->lugar;
    }

    public function setLugar($lugar) {
        $this->lugar = $lugar;
        return $this;
    }

    public function getObservacionesEstado() {
        return $this->observaciones_estado;
    }

    public function setObservacionesEstado($observaciones_estado) {
        $this->observaciones_estado = $observaciones_estado;
        return $this;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
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