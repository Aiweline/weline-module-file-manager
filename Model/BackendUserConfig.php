<?php

namespace Weline\FileManager\Model;

class BackendUserConfig extends \Weline\Backend\Model\BackendUserConfig
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getConfig('file-manager')) {
            $this->setConfig('file-manager', 'local');
        }
    }
}
