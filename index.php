<?php
require_once(dirname(__FILE__) . '/include/config.php');
require_once(dirname(__FILE__) . '/include/autoload.php');
$removed = $info = $deck = $removedcount = null;
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && isset($_POST['archidekt'])){
	$convertor = new convertor();
	$result = $convertor->convertArchidektToArena($_POST['archidekt']);
	$removed = &$result['removed'];
	$info = &$result['info'];
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
			<a href="/"><h1>ArenaDekt</h1></a>
		</div>
		<?php if(empty($deck)): ?>
		<div class="item" id="help">
			<h2>Instructions</h2>
			<ol>
				<li>This is a tool to help convert your archidekt deck into a Historic Brawl format to be imported into MTG Arena</li>
				<li>Open your deck on <a href="https://archidekt.com/" target="_blank">archidekt.com</a></li>
				<li>Go To "Extras" and click "Export Deck"</li>
				<li>Export options should read "1 Example Card", if it doesn't click it and select "Uncheck All"</li>
				<li>Ensure Export type is set to "Text" and click the "Copy" button</li>
				<li>Come back here, paste it below and hit the "Convert" button</li>
				<li>Copy the text from the text box, open the "Decks" tab on Arena and use the "Import" button</li>
			</ol>
		</div>
		<?php endif; ?>
		<?php if(!empty($info)): ?>
		<div class="item" id="info">
			<?=nl2br($info)?>
		</div>
		<?php endif; ?>
		<div class="item" id="form">
			<form method="POST">
				<textarea name="archidekt" placeholder="Paste exported archidekt data here&#10;e.g.&#10;1 Mountain&#10;1 Goblin Javelineer" autofocus required rows="10"><?=$deck?></textarea>
				<?php if(empty($deck)): ?>
				<br/>
				<input type="submit" value="Convert"/>
				<?php endif; ?>
			</form>
		</div>
		<?php if(!empty($removed)): ?>
		<div class="item" id="removed">
			Removed <?=$removedcount?> cards<br/>
			<?=nl2br($removed)?>
		</div>
		<?php endif; ?>
	</div>
</body>
</html>