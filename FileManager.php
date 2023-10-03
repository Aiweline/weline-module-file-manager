<?php

namespace Weline\FileManager;

use Weline\Framework\DataObject\DataObject;

abstract class FileManager extends DataObject implements FileManagerInterface
{
    use  FileManagerTrait;
}
