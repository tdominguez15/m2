<?php

namespace Southbay\ReturnProduct\Cron;

class RtvCheckStatusDocumentsCronModel
{
    private $sapRtvRequest;

    public function __construct(\Southbay\ReturnProduct\Helper\SendSapRtvRequest $sapRtvRequest)
    {
        $this->sapRtvRequest = $sapRtvRequest;
    }

    public function execute()
    {
        /**
         * Ejecuta el proceso que verifica si hay documentos de rtv que estan pendientes de
         */
        $this->sapRtvRequest->checkSapInterfacePendingToEnd();
    }
}
