<?php
declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 作者：Administrator
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 * 日期：25/4/2024 15:29:13
 */

namespace Weline\FileManager\Ui\EavModel\Select;

use Weline\Eav\EavModelInterface;
use Weline\Eav\Model\EavAttribute;
use Weline\FileManager\Taglib\FileManager;
use Weline\Framework\View\Template;
use function PHPUnit\Framework\callback;

class File implements EavModelInterface
{

    function getHtml(EavAttribute\Type &$type, mixed &$value, string &$label_class, array &$attrs, array &$option_items = []): string
    {
        $defaults    = [
            'target' => '#' . $type->getCode() . '-file',
            'value' => $value,
        ];
        $attr        = array_merge($this->getModelData(), $defaults, $attrs);
        $id          = str_replace('#', '', $attr['target']);
        $title       = $attr['title'];
        $func        = FileManager::callback();
        $params      = [
            'file-manager', [], [], $attr
        ];
        $attr_string = '';
        foreach ($attr as $k => $v) {
            if ($k != 'target') {
                $attr_string .= ' ' . $k . '="' . $v . '"';
            }
        }
        $res = call_user_func($func, ...$params);
        $res = str_replace('<?php', '', $res);
        $res = str_replace('?>', '', $res);
        ob_start();
        eval($res);
        $frontendAttrs = $type->getFrontendAttrs();
        $download      = '';
        if ($value) {
            $file_name = basename($value);
            $download  = __('已选:') .'<a title="' . __("点击下载") . '" href="' . $value . '" download>' . $file_name . '</a>';
        }
        return '<label class="' . $label_class . '" for="' . $id . '">' . $title . '  ' .  $download . '</label><input ' . $attr_string . '  name="' . $type->getCode() . '" value="' . $value . '" id="' . $id . '" ' . $frontendAttrs . ' type="text" >' . ob_get_clean();
    }

    public function getModelData(): mixed
    {
        return [
            'var' => '',
            'path' => 'media/uploader/',
            'title' => '从文件管理器选择',
            'multi' => '0',
            'ext' => 'xls,xlsx',
            'w' => '50',
            'h' => '50',
            'size' => '102400',
        ];
    }
}