<?php $this->beginBlock('title'); ?>Title of Template<?php $this->endBlock();?>

<?php $this->beginBlock('body');?>
<?php $this->partial('test', $partial_params);?>
<?php $this->component('test', $comp_params);?>
<?php $this->endBlock();?>