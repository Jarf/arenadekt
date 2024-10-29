<?php
require_once(dirname(__FILE__) . '/include/config.php');
require_once(dirname(__FILE__) . '/include/autoload.php');
$info = $deck = $removedcount = null;
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && isset($_POST['archidekt'])){
	$convertor = new convertor();
	$result = $convertor->convertArchidektToArena($_POST['archidekt']);
	$info = &$result['removed'];
	$deck = &$result['arenadeck'];
	$removedcount = &$result['removedcount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ArenaDekt</title>
	<meta name="description" content="Convert archidekt to arena">
	<link rel="stylesheet" rel="preload" as="style" type="text/css" href="<?=SITE_CSS?>main.css">
</head>
<body>
	<div id="container">
		<div class="item" id="title">
			<h1>ArenaDekt</h1>
		</div>
		<div class="item" id="form">
			<form method="POST">
				<textarea name="archidekt" placeholder="Paste exported archidekt data here" autofocus required rows="10"><?=$deck?></textarea>
				<?php if(empty($deck)): ?>
				<br/>
				<input type="submit" value="Convert"/>
				<?php endif; ?>
			</form>
		</div>
		<?php if(!empty($info)): ?>
		<div class="item" id="info">
			Removed <?=$removedcount?> cards<br/>
			<?=nl2br($info)?>
		</div>
		<?php endif; ?>
	</div>
</body>
</html>