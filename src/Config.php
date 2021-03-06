<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午5:46
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\Spl\SplArray;

class Config
{
    protected $conf;

    use Singleton;

    public function __construct()
    {
        $this->conf = new SplArray();
    }

    /**
     * 获取配置项
     * @param string $keyPath 配置项名称 支持点语法
     * @return array|mixed|null
     */
    public function getConf($keyPath = '')
    {
        if ($keyPath == '') {
            return $this->toArray();
        }
        return $this->conf->get($keyPath);
    }

    /**
     * 设置配置项
     * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
     * @param string $keyPath 配置项名称 支持点语法
     * @param mixed  $data    配置项数据
     */
    public function setConf($keyPath, $data): void
    {
        $this->conf->set($keyPath, $data);
    }

    /**
     * 获取全部配置项
     * @return array
     */
    public function toArray(): array
    {
        return $this->conf->getArrayCopy();
    }

    /**
     * 覆盖配置项
     * @param array $conf 配置项数组
     */
    public function load(array $conf): void
    {
        $this->conf = new SplArray($conf);
    }

    /**
     * 载入一个文件的配置项
     * @param string $filePath 配置文件路径
     * @param bool   $merge    是否将内容合并入主配置
     * @author : evalor <master@evalor.cn>
     */
    public function loadFile($filePath, $merge = false)
    {
        if (is_file($filePath)) {
            $confData = require_once $filePath;
            if (is_array($confData) && !empty($confData)) {
                $basename = strtolower(basename($filePath, '.php'));
                if (!$merge) {
                    $this->conf[$basename] = $confData;
                } else {
                    $this->conf = new SplArray(array_merge($this->toArray(), $confData));
                }
            }
        }
    }

    public function loadEnv(string $file)
    {
        $defines = get_defined_constants();
        if(file_exists($file)){
            $file = file($file);
            foreach ($file as $line){
                $line = trim($line);
                if(!empty($line)){
                    //若以 # 开头的则为注释，不解析
                    if(strpos($line,"#") !== 0){
                        $arr = explode('=',$line);
                        if(!empty($arr)){
                            $val = trim(explode("#",$arr[1])[0]);
                            foreach ($defines as $key => $define){
                                $val = str_replace($key,$define,$val);
                            }
                            if(is_numeric($val) && is_int($val + 0)){
                                $val = (int)$val;
                            }else if(is_string($val)){
                                if($val== 'null' || empty($val)){
                                    $val = null;
                                }else if($val == 'true'){
                                    $val = true;
                                }else if($val == 'false'){
                                    $val = false;
                                }
                            }
                            $this->setConf(trim($arr[0]),$val);
                        }
                    }
                }
            }
        }else{
            throw new \Exception("config file : {$file} is miss");
        }
    }
}
