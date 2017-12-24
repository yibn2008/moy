<ul id="sub_nav">
<?php foreach ($nav_list as $act => $label):?>
  <li<?php echo $act == $selected ? ' class="selected"' : null;?>><a href="<?php echo m_url("generator:$act");?>"><?php echo $label;?></a></li>
<?php endforeach;?>
</ul>