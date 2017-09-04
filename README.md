[![Latest Stable Version](https://poser.pugx.org/roferwoo/tp5-smarty/v/stable)](https://packagist.org/packages/roferwoo/tp5-smarty)
[![Total Downloads](https://poser.pugx.org/roferwoo/tp5-smarty/downloads)](https://packagist.org/packages/roferwoo/tp5-smarty)
[![Latest Unstable Version](https://poser.pugx.org/roferwoo/tp5-smarty/v/unstable)](https://packagist.org/packages/roferwoo/tp5-smarty)
[![License](https://poser.pugx.org/roferwoo/tp5-smarty/license)](https://packagist.org/packages/roferwoo/tp5-smarty)


# tp5-smarty
ThinkPHP5 Smarty 引擎驱动，支持模板主题与模板布局配置。

## 安装方法
使用composer安装模版引擎方法：
```
composer require roferwoo/tp5-smarty
```

## 配置参数
在ThinkPHP5的基础上，可添加配置以下参数：
```
// 模板主题目录
'view_theme'   => 'default',
// 模板布局
'page_layout' => [
    'header' => 'path/to/header.html', // 或 'no_header' => 1,
    'footer' => 'path/to/footer.html', // 或 'no_footer' => 1,
],
```
