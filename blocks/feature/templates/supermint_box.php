<?php 
defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
$pageTheme = $c->getCollectionThemeObject();
$color = $pageTheme->getClassSettings($b,'icon-color');
if ($color) {
	$contrast = "color:" . $pageTheme->contrast('#' . $color);
	$color = "color:#$color";
}
$size = $pageTheme->getClassSettings($b,'icon-size');
$size = $size ? "icon-size-$size" : '';
$title = $linkURL ? ('<a href="' . $linkURL . '">' . h($title) . '</a>') : h($title);
?>
<div class="feature-box full feature-box_content <?php echo $size ?>">
	<h3 class="feature-box_icon">
		<span class="fa-stack <?php echo $size ?>">
		  <i class="fa fa-circle fa-stack-2x fa-colored" <?php echo $color ? "style='$color'" : ''; ?>></i>
		  <i class="fa fa-<?php echo $icon?> fa-contrast fa-stack-1x" <?php echo $color ? "style='$contrast'" : ''; ?>></i>
		</span>
	</h3>
	<h3 class="feature-box_content_title"><?php  echo $title ?></h3>
	<?php if ($paragraph) : ?><p><?php echo $paragraph?></p><?php endif ?>
</div>
