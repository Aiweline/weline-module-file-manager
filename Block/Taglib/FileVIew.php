<?php

namespace Weline\FileManager\Block\Taglib;

use Weline\FileManager\Helper\Image;

class FileVIew extends \Weline\Framework\View\Block
{
    protected string $_template = 'Weline_FileManager::image-preview/template-only-view.phtml';

    function __init()
    {
        parent::__init();
        $value = $this->getParseVarsParams('value');
        $this->assign('id', 'preview-'.md5($value));
        $this->assign('value_items', Image::processImagesValuePreviewData($value, $this->getData('width'), $this->getData('height')));
    }
}