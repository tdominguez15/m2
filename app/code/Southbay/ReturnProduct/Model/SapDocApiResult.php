<?php

namespace Southbay\ReturnProduct\Model;

class SapDocApiResult
{
    public string $estado;
    public string $mensaje;

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
    public function getMensaje(): string
    {
        return $this->mensaje;
    }

    /**
     * @param string $mensaje
     * @return void
     */
    public function setMensaje(string $mensaje): void
    {
        $this->mensaje = $mensaje;
    }
}
