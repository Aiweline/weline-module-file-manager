<?php

namespace Weline\FileManager\Block;

use Weline\FileManager\Helper\Image;
use Weline\Framework\View\Block;

class FileManager extends Block
{
    public function render(): string
    {
        $value = $this->getParseVarsParams('value');
        $this->assign('value', $value);
        $size_alias = $this->getSize($this->getData('size'));
        $value = $this->getData('value') ?: '';
        $this->assign('value_items', Image::processImagesValuePreviewData($value, $this->getData('width'), $this->getData('height')));
        $this->assign('size_alias', $size_alias);
        return parent::render();
    }

    public function getParams()
    {
        return [
            'isIframe' => true,
            'targetElement' => $this->getData('target'),
            'startPath' => $this->getData('path'),
            'multi' => $this->getData('multi'),
            'ext' => $this->getData('ext'),
            'size' => $this->getData('size'),
        ];
    }

    public function getSize($filesize)
    {
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' bit';
        }
        return $filesize;
    }

    public function doc()
    {
        return \Weline\FileManager\Taglib\FileManager::document();
    }
}
