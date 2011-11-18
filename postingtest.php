<?php

// Enter board name
$board = "b";
//enter a non zero number to simulate replying
$parentid = 0;
?>

<?='<?xml version="1.0" encoding="UTF-8"?>'?>
<html>
	<head>
		<title>Kusaba X 1.0 posting module test</title>
	</head>
	<body>
		<form
			action="/index.php?app=core&module=post&section=post"
			method="POST" enctype="multipart/form-data">
			<input type="hidden" name="board" value="<?php echo $board ?>" />
			<input type="hidden" name="replythread" value="<?php echo $parentid ?>" />
			<input type="hidden" name="MAX_FILE_SIZE" value="7340032" />
			<div>
				Email
				<input type="text" id="email" name="em" size="28" maxlength="75"
					accesskey="e" />
			</div>
			<div>
				Subject
				<input type="text" id="subject" name="subject" size="35"
					maxlength="75" accesskey="s" />&nbsp;
				<input type="submit" value="Submit" accesskey="z" />
			</div>
			<div>
				Message:
				<textarea id="message" name="message" cols="48" rows="4"
					accesskey="m"></textarea>
			</div>
			<div>
				File
				<input id="file" type="file" name="imagefile[]" size="35"
					accesskey="f" />
			</div>
		</form>
	</body>
</html>
