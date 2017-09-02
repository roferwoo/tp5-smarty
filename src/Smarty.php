<?php
/**
 * ThinkPHP5 Smarty驱动器
 *
 * 支持模块主题配置
 *
 * Date: 2017/9/2 上午9:48
 * @category  PHP
 * @package   woophp
 * @author    Rofer Woo (roferwoo@gamil.com)
 * @copyright 2017 woophp.xyz
 * @version   0.0.1
 */

namespace think\view\driver;

use think\App;
use think\exception\TemplateNotFoundException;
use think\Loader;
use think\Log;
use think\Request;
use Smarty as LibSmarty;

class Smarty
{
    // 模板引擎实例
    private $template = null;
    // 模板主题路径
    private $theme = '';
    // 模板引擎参数
    protected $config = [
        // 模板主题目录
        'view_theme'   => 'default',
        // 视图基础目录（集中式）
        'view_base'   => '',
        // 模板起始路径
        'view_path'   => '',
        // 模板文件后缀
        'view_suffix' => 'php',
        // 模板文件名分隔符
        'view_depr'   => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 模板编译目录
        'temp_path'   => RUNTIME_PATH . 'temp' . DS,
        // 模板缓存目录
        'cache_path' => RUNTIME_PATH . 'cache' . DS,
        'cache_prefix' => '',
        'cache_suffix' => '.php',
    ];

    /**
     * 构造函数
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->theme = !empty($this->config['view_theme']) ? $this->config['view_theme'] . DS : '';
        if (empty($this->config['view_path'])) {
            $this->config['view_path'] = App::$modulePath . 'view' . DS . $this->theme;
        }

        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = RUNTIME_PATH . 'cache' . DS;
        }

        $this->template = new LibSmarty();
        $this->template->setLeftDelimiter($this->config['tpl_begin']);
        $this->template->setRightDelimiter($this->config['tpl_end']);
        $this->template->setCaching(!App::$debug);
        // 是否强制编译
        $this->template->setForceCompile(!App::$debug);
        // 设置模板目录
        $this->template->setTemplateDir($this->config['view_path']);
        // 合并编译
        $this->template->merge_compiled_includes = true;
        // 设置缓存目录
        $this->template->setCacheDir($this->config['cache_path']);
        // 设置编译目录
        $this->template->setCompileDir($this->config['temp_path']);
    }

    /**
     * 检测是否存在模板文件
     * @access public
     * @param string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists($template)
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        return is_file($template);
    }

    /**
     * 渲染模板文件
     * @access public
     * @param string    $template 模板文件
     * @param array     $data 模板变量
     * @param array     $config 模板参数
     * @return void
     */
    public function fetch($template, $data = [], $config = [])
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }
        // 记录视图信息
        App::$debug && Log::record('[ VIEW ] ' . $template . ' [ ' . var_export(array_keys($data), true) . ' ]', 'info');
        $this->template->fetch($template, $data, $config);
    }

    /**
     * 渲染模板内容
     * @access public
     * @param string    $template 模板内容
     * @param array     $data 模板变量
     * @param array     $config 模板参数
     * @return void
     */
    public function display($template, $data = [], $config = [])
    {
        $this->template->display($template, $data, $config);
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        // 分析模板文件规则
        $request = Request::instance();
        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($module, $template) = explode('@', $template);
        }
        if ($this->config['view_base']) {
            // 基础视图目录
            $module = isset($module) ? $module : $request->module();
            $path   = $this->config['view_base'] . ($module ? $module . DS : '');
        } else {
            $path = isset($module) ? APP_PATH . $module . DS . 'view' . DS : $this->config['view_path'];
        }

        $depr = $this->config['view_depr'];
        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = Loader::parseName($request->controller());
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DS, $controller) . $depr . $request->action();
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DS, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }
        return $path . $this->theme . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->template, $method], $params);
    }
}