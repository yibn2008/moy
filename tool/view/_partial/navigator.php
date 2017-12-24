<ul id="top_nav">
<?php foreach ($nav_list as $ctrlr => $label):?>
  <li<?php echo $ctrlr == $selected ? ' class="selected"' : null;?>><a href="<?php echo m_url("$ctrlr:index");?>"><?php echo $label;?></a></li>
<?php endforeach;?>
</ul>