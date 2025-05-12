<?php

namespace Southbay\ReturnProduct\Model;

class SapOrderConfirmationApiResult
{
    public string $estado;
    public string $detalle;
    public string $codigo;
    public string $referencia1;
    public string $referencia2;
    public string $referencia3;

    /**
     * @return string
     */
    public function getEstado(): string
    {
        return $this->estado;
    }


    /**
     * @param string $estado
     * @return void
     */
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }

    /**
     * @return string
     */
    public function getDetalle()
    {
        return $this->detalle;
    }


    /**
     * @param string $detalle
     * @return void
     */
    public function setDetalle($detalle): void
    {
        $this->detalle = $detalle;
    }

    /**
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param string $codigo
     * @return void
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * @return string
     */
    public function getReferencia1()
    {
        return $this->referencia1;
    }

    /**
     * @param string $referencia1
     * @return void
     */
    public function setReferencia1($referencia1)
    {
        $this->referencia1 = $referencia1;
    }

    /**
     * @return string
     */
    public function getReferencia2()
    {
        return $this->referencia2;
    }

    /**
     * @param string $referencia2
     * @return void
     */
    public function setReferencia2($referencia2)
    {
        $this->referencia2 = $referencia2;
    }

    /**
     * @return string
     */
    public function getReferencia3()
    {
        return $this->referencia3;
    }

    /**
     * @param string $referencia3
     * @return void
     */
    public function setReferencia3($referencia3)
    {
        $this->referencia3 = $referencia3;
    }
}
