<?php

class B1_Acccounting_Exception extends Exception
{

    private $extraData;

    public function __construct($message = "", $extraData = array(), $code = 0, \Exception $previous = null)
    {
        $this->extraData = $extraData;
        parent::__construct($message, $code, $previous);
    }

    public function getExtraData()
    {
        return $this->extraData;
    }

}
