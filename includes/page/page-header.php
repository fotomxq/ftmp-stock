<?php

/**
 * 标准页面头引用
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package page
 */

/**
 * 确保定义存在相关定义
 * 需要变量:
 *  标题变量 header-title
 *  引用css样式表文件 header-css
 *  全局引用CSS样式文件 glob-css
 */
if(!isset($pageSets)){
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
  <!-- Standard Meta -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <!-- Site Properities -->
  <link rel="shortcut icon" href="../assets/imgs/logo.ico" type="image/x-icon">
  <title><?php echo $pageSets['header-title']; ?></title>
  <link rel="stylesheet" type="text/css" href="../assets/css/semantic.css">
  <?php if(isset($pageSets['glob-css'])){ foreach($pageSets['glob-css'] as $v){ ?>
  <link rel="stylesheet" type="text/css" href="../assets/css/<?php echo $v; ?>">
  <?php } } ?>
  <?php if(isset($pageSets['header-css'])){ foreach($pageSets['header-css'] as $v){ ?>
  <link rel="stylesheet" type="text/css" href="assets/css/<?php echo $v; ?>">
  <?php } } ?>
  </head>
<body>