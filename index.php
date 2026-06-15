<?php
require_once(dirname(__FILE__) . '/include/config.php');
require_once(dirname(__FILE__) . '/include/autoload.php');
$removed = $info = $deck = $removedcount = $replaced = $replacedcount = null;
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST) && isset($_POST['archidekt'])){
	$convertor = new convertor();
	$result = $convertor->convertArchidektToArena($_POST['archidekt']);
	$removed = &$result['removed'];
	$info = &$result['info'];
	$deck = &$result['arenadeck'];
	$removedcount = &$result['removedcount'];
	$replaced = &$result['replaced'];
	$replacedcount = &$result['replacedcount'];
}
$lastupdate = new db();
$lastupdate->query('SELECT lastmodified FROM import LIMIT 1');
$lastupdate->execute();
if($lastupdate->rowCount() === 1){
	$lastupdate = $lastupdate->fetch();
	$lastupdate = $lastupdate->lastmodified;
	$lastupdate = DateTime::createFromFormat('Y-m-d H:i:s', $lastupdate);
	$lastupdate = $lastupdate->format('F jS, Y');
}else{
	$lastupdate = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ArenaDekt</title>
	<meta name="description" content="Convert Archidekt into MTG Arena Historic Brawl format">
	<link rel="preload" href="<?=SITE_FONT?>planewalker-webfont.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high"/>
	<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
	<link rel="shortcut icon" href="/favicon/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
	<meta name="apple-mobile-web-app-title" content="ArenaDekt" />
	<link rel="manifest" href="/favicon/site.webmanifest" />
	<style>
		@font-face {
		    font-family: 'planewalkerregular';
		    src: url('/fonts/planewalker-webfont.woff2') format('woff2'),
		         url('/fonts/planewalker-webfont.woff') format('woff');
		    font-weight: normal;
		    font-style: normal;
		    font-display: swap;
		}

		html{
			font-family: 'planewalkerregular';
			color: #000000;
			box-shadow: 2px 3px 20px black, 0 0 60px #8a4d0f inset;
			background: #FCF5E5;
			min-height: 100%;
		}

		div#container{
			display: flex;
			flex-direction: column;
			flex-wrap: nowrap;
			justify-content: center;
			align-content: center;
			align-items: center;
			div.item{
				width: 600px;
				max-width: 100%;
				h1, form, textarea, input, button{
					width: 100%;
				}
				h1, a + span{
					display: block;
					text-align: center;
				}
				h1, h2{
					margin: 0;
				}
				textarea{
					resize: none;
					padding: 0;
					border: 0;
					white-space: pre-line;
					padding: 8px;
					width: calc(100% - 16px);
				}
				input, a#reset, button#copy{
					font-family: 'planewalkerregular';
					color: #000000;
					box-shadow: 0 0 60px #8a4d0f inset;
					background: #FCF5E5;
					cursor: pointer;
					text-decoration: none;
					display: block;
					text-align: center;
					border: 1px solid #000000;
					margin-bottom: 8px;
				}
			}div.item#title a{
				text-decoration: none;
				color: #000000;
			}
		}
	</style>
</head>
<body>
	<div id="container" role="main">
		<div class="item" id="title">
			<a href="/"><h1>ArenaDekt</h1></a>
			<?php if($lastupdate !== null): ?>
			<span>Last Updated: <?=$lastupdate?></span>
			<?php endif; ?>
		</div>
		<?php if(empty($deck)): ?>
		<div class="item" id="help">
			<h2>Instructions</h2>
			<ol>
				<li>This is a tool to help convert your archidekt deck into a Historic Brawl format to be imported into MTG Arena</li>
				<li>Open your deck on <a href="https://archidekt.com/" target="_blank">archidekt.com</a></li>
				<li>Go To "Extras" and click "Export Deck"</li>
				<li>Ensure "Include out of deck cards" is not checked</li>
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
			<?php if(!empty($deck)): ?>
				<button id="copy">Copy To Clipboard</button>
				<a id="reset" href="/">Go Again</a>
			<?php endif; ?>
		</div>
		<?php if(!empty($removed)): ?>
		<div class="item" id="removed">
			<b>Removed <?=$removedcount?> card(s)</b><br/>
			<?=nl2br($removed)?>
		</div>
		<?php endif; ?>
		<?php if(!empty($replaced)): ?>
		<div class="item" id="replaced">
			<b>Replaced <?=$replacedcount?> card(s) with Alchemy equivalents/double sided card front names</b><br/>
			<?=nl2br($replaced)?>
		</div>
		<?php endif; ?>
	</div>
	<script>
		if (window.trustedTypes && window.trustedTypes.createPolicy) {
			window.trustedTypes.createPolicy('default', {
				createHTML: string => string,
				createScriptURL: string => string,
				createScript: string => string
			});
		}
		var el = document.getElementById("copy");
		if(el !== null){
			el.onclick = function(){
				document.querySelector("textarea").select();
				document.execCommand("copy");
			}	
		}
	</script>
</body>
</html>