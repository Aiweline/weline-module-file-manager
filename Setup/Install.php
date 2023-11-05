<?php

namespace Weline\FileManager\Setup;

use Weline\FileManager\Model\BackendUserConfig;
use Weline\Framework\Setup\Data;
use Weline\Framework\Setup\InstallInterface;

class Install implements InstallInterface
{
    private BackendUserConfig $backendUserConfig;

    public function __construct(BackendUserConfig $backendUserConfig)
    {
        $this->backendUserConfig = $backendUserConfig;
    }

    /**
     * @inheritDoc
     */
    public function setup(Data\Setup $setup, Data\Context $context): void
    {
        if (!$this->backendUserConfig->getDefaultConfig('file-manager')) {
            $this->backendUserConfig->setDefaultConfig('file-manager', 'local', 'Weline_FileManager', '文件管理器配置');
        }
    }
}
