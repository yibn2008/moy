<html>
<head>
<title><?php $this->beginBlock('title');?>Title of Layout<?php $this->endBlock();?></title>
<?php $this->styles();?>
<?php $this->scripts();?>
</head>
<body>
<h1><?php echo $title;?></h1>
<div><?php $this->beginBlock('body');?>Body of Layout<?php $this->endBlock();?></div>
</body>
</html>