<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Moy工具箱</title>
<link rel="stylesheet" type="text/css" href="<?php echo m_url('/css/global.css');?>" />
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <div class="inner_wrap">
        <div id="logo">
          <a href="<?php echo m_url('default:index');?>"><img src="<?php echo m_url('/img/moy-tool.png');?>" alt="Moy Framework" /></a>
        </div>
        <?php $this->component('navigator');?>
      </div>
    </div>
    <div id="body">
      <?php $this->beginBlock('body');?>
      <div id="main">Default ....</div>
      <?php $this->endBlock();?>
      <div id="copyright">
        Copyright &copy; 2012 <a href="#">phpmoy.org</a>
      </div>
    </div>
  </div>
</body>
</html>
