<?php
// src/Domain/Entity/PrestamoDetalle.php
namespace App\Domain\Entity;

class PrestamoDetalle extends AbstractEntity
{
    protected $prestamo_id;
    protected $bien_id;
    protected $cantidad;

    public function getPrestamoId() {
        return $this->prestamo_id;
    }

    public function setPrestamoId($prestamo_id) {
        $this->prestamo_id = $prestamo_id;
        return $this;
    }

    public function getBienId() {
        return $this->bien_id;
    }

    public function setBienId($bien_id) {
        $this->bien_id = $bien_id;
        return $this;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
        return $this;
    }
}