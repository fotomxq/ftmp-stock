<?php

/**
 * 标准页面脚引用
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package page
 */

/**
 * 确保定义存在相关定义
 * 需要变量:引用JS footer-js / 全局应用JS glob-js
 */
if(!isset($pageSets)){
    die();
}
?>
<div class="ui inverted vertical footer segment"></div>
  <script src="../assets/js/jquery.js"></script>
  <script src="../assets/js/semantic.js"></script>
  <?php if(isset($pageSets['glob-js'])){ foreach($pageSets['glob-js'] as $v){ ?>
  <script src="../assets/js/<?php echo $v; ?>"></script>
  <?php } } ?>
  <?php if(isset($pageSets['footer-js'])){ foreach($pageSets['footer-js'] as $v){ ?>
  <script src="assets/js/<?php echo $v; ?>"></script>
  <?php } } ?>
</body>
</html>