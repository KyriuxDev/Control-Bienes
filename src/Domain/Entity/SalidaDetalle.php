<?php
// src/Domain/Entity/SalidaDetalle.php
namespace App\Domain\Entity;

class SalidaDetalle extends AbstractEntity
{
    protected $salida_id;
    protected $bien_id;
    protected $cantidad;

    public function getSalidaId() {
        return $this->salida_id;
    }

    public function setSalidaId($salida_id) {
        $this->salida_id = $salida_id;
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