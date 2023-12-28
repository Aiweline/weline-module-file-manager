<?php

namespace Weline\FileManager\Taglib;

use Weline\Backend\Session\BackendSession;
use Weline\FileManager\Cache\FileManagerCacheFactory;
use Weline\FileManager\FileManagerInterface;
use Weline\FileManager\Model\BackendUserConfig;
use Weline\Framework\App\Env;
use Weline\Framework\Cache\CacheInterface;
use Weline\Framework\Http\Request;
use Weline\Framework\Manager\MessageManager;
use Weline\Framework\Manager\ObjectManager;
use Weline\Framework\System\File\Io\File;
use Weline\Framework\System\File\Scan;
use Weline\Framework\System\ModuleFileReader;
use Weline\Taglib\TaglibInterface;

class FileManagerConnector implements TaglibInterface
{
    /**
     * @inheritDoc
     */
    public static function name(): string
    {
        return 'file-manager-connector';
    }

    /**
     * @inheritDoc
     */
    public static function tag(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function attr(): array
    {
        return [
            'target' => false,
            'title' => false,
            'path' => true,
            'ext' => true,
            'value' => false,
            'vars' => false,
            'multi' => false,
            'w' => false,
            'h' => false,
            'size' => false,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function tag_start(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function tag_end(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function callback(): callable
    {
        return function ($tag_key, $config, $tag_data, $attributes) {
            if(empty($attributes)){
                $input = "target='#111', title='文件管理器', var='store', path='store/logo', value='store.logo', multi='0', ext='jpg,png,gif,webp', w='50', h='50'";
                $pattern = '/(\w+)\s*=\s*[\'"]?([^\'"]*)[\'"]?/';
                preg_match_all($pattern, $input, $matches);
                $outputArray = array();
                foreach ($matches[1] as $index => $match) {
                    $outputArray[$match] = $matches[2][$index];
                }
                $attributes = array_merge($outputArray, $attributes);
            }

            # 检查是否有配置默认的文件管理器
            $userConfigFileManager = ObjectManager::getInstance(BackendUserConfig::class)->getConfig('file_manager') ?: 'local';
            $cacheKey = json_encode(func_get_args()) . $userConfigFileManager;
            /**@var CacheInterface $cache */
            $cache = ObjectManager::getInstance(FileManagerCacheFactory::class);
            $result = $cache->get($cacheKey);
            if ($result and ($userConfigFileManager !== 'local')) {
                return $result;
            }
            /**@var Scan $fileScan $ */
            $fileScan = ObjectManager::getInstance(Scan::class);
            $fileManagers = [];
            $modules = Env::getInstance()->getActiveModules();
            foreach ($modules as $module) {
                $files = [];
                $fileScan->globFile(
                    $module['base_path'] . 'FileManager',
                    $files,
                    '.php',
                    $module['base_path'],
                    $module['namespace_path'] . '\\',
                    '.php',
                    true
                );
                foreach ($files as $file) {
                    $class = ObjectManager::getInstance($file);
                    if ($class instanceof FileManagerInterface) {
                        $fileManagers[$class::name()] = $class;
                    }
                }
            }
            if (count($fileManagers) > 1 and $userConfigFileManager === 'local') {
                /**@var \Weline\FileManager\FileManager $fileManager */
                $fileManager = array_pop($fileManagers);
            } else {
                if (!isset($fileManagers[$userConfigFileManager])) {
                    ObjectManager::getInstance(MessageManager::class)->addWarning(__('配置的文件管理器不存在! 文件管理器名：%1', $userConfigFileManager));
                    # 使用第一个文件管理器作为默认的文件管理器
                    /**@var \Weline\FileManager\FileManager $fileManager */
                    $fileManager = array_shift($fileManagers);
                } else {
                    /**@var \Weline\FileManager\FileManager $fileManager */
                    $fileManager = $fileManagers[$userConfigFileManager];
                }
            }
            if (!isset($attributes['target'])) {
                throw new \Exception(__('缺少目标ID。文档：%1', self::document()));
            }
            $fileManager
                ->setTarget(trim($attributes['target'], '.#'))
                ->setPath($attributes['path'] ?? '')
                ->setValue($attributes['value'] ?? '')
                ->setTitle($attributes['title'] ?? '')
                ->setMulti($attributes['multi'] ?? '')
                ->setWidth($attributes['w'] ?? 50)
                ->setHeight($attributes['h'] ?? 50)
                ->setExt($attributes['ext'] ?? '*')
                ->setSize($attributes['size'] ?? '102400')
                ->setVars($attributes['vars'] ?? '');
            $result = $fileManager->setData(
                ['tag_key' => $tag_key,
                    'tag_data' => $tag_data,
                    'attributes' => $attributes
                ]
            )->getConnector();
            $cache->set($cacheKey, $result);
            return $result;
        };
    }

    /**
     * @inheritDoc
     */
    public static function tag_self_close(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function tag_self_close_with_attrs(): bool
    {
        return true;
    }

    public static function document(): string
    {
        $doc = htmlentities(
            "<file-manager-cpnnector>target='#demo',title='文件管理器',var='store',path='store/logo',value='store.logo',multi='0',ext='jpg,png,gif,webp',w='50',h='50'</file-manager-cpnnector>
            或者<br>
            @file-manager-cpnnector{target='#demo',title='文件管理器',var='store',path='store/logo',value='store.logo',multi='0',ext='jpg,png,gif,webp',w='50',h='50'}
            或者<br>
            <file-manager-cpnnector target='#demo' title='文件管理器' var='store' path='store/logo' value='store.logo' multi='0' ext='jpg,png,gif,webp' w='50' h='50'/>
            或者<br>
            @file-manager-cpnnector(target='#demo',title='文件管理器',var='store',path='store/logo',value='store.logo',multi='0',ext='jpg,png,gif,webp',w='50',h='50')
            "
        );
        return <<<HTML
使用方法：
{$doc}
参数解释：
target：可选【必须链接到URL上】。文件管理器回填目标。
ext：必选。默认jpg,png,gif,webp格式
path：必选。默认打开的文件路径
title：可选。文件管理器标题
vars：可选。当前变量
value：可选。默认当前的文件路径
multi：可选。默认单选
w：可选。默认预览宽50px
h：可选。默认预览高50px
HTML;
    }
}
