<?php

namespace Phwoolcon\Auth\Model;

use Exception;

interface UserCheckStatusInterface
{

    /**
     * @return true
     * @throws Exception
     */
    public function checkStatus();
}
