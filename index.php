<?

$q    = trim($_GET['q']);
$lang = $_GET['lang'];
$shamsiya_letters = array("ز", "ر", "ذ", "د", "ث", "ت", "ن", "ل", "ظ", "ط", "ض", "ص", "ش", "س");

## from http://php.net/manual/en/function.str-split.php
function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

## load the map
$output = "";

## TK: How to handle short vowels??

if(isset($q)) {
	## choose language
	$langStr = "ijmes-a";
	if ($lang == "Persian") {
		$langStr = "ijmes-p";
	}

	## load map
	$map = json_decode( file_get_contents('charmap.json'), $assoc = true );

	## prep the words
	$words  = explode(" ", $q);

	foreach ($words as $word) {
		$letters = str_split_unicode($word);

		for ($i = 0; $i < count($letters); $i++) {
			// do the look-ahead
			$curr = $letters[$i];
			$next = "";
			if ($i + 1 < count($letters)) {
				$next = $letters[$i + 1];
			}

			if(0) {
			} else if ($i == 0 and $curr == "ا" and $next == "ل") {
				## add al-
				## TK: this behavior is different in Persian

				if ($i + 2 < count($letters)) {
					## handle shamsiya letters 
					$after_next = $letters[$i + 2];
					if (in_array($after_next, $shamsiya_letters)){
						$shams = $map[$after_next][$langStr];
						$output = $output . "a". $shams . "-";
					} else{
					$output = $output . "al-";
				}
				} else{
					$output = $output . "al-";
				}
				
				$i++;
			} else if ($curr == "و" or $curr == "ي") {
				## This is a ya or waw. decide whether to use a consonant or vowel.
				## Testing is very basic here -- if next letter is a vowel, use consonant.
				## Otherwise, use long vowel.
				## TK: There are probably other cases here but this is the most basic.

				if ( ($next == "ا" or $next == "و" or $next == "ي") and $next != "") {
					$index = $map[$curr]['name'] . '-c';
				} else {
					$index = $map[$curr]['name'] . '-v';
				}

				$output = $output . $map[$index][$langStr];
			} else if ( isset($map[$curr][$langStr]) ) {
				$output = $output . $map[$curr][$langStr];
			} else {
				$output = $output . $curr;
			}
		}
		$output = $output . " ";
	}
}

?>
<html>
	<head>
		<title>Arabic/Persian to IJMES Transliterator (APIT)</title>
		<meta charset="UTF-8" />
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
	    <link rel="stylesheet" type="text/css" href="main.css" /> 
	</head>

	<body>
		<div class="main">
			<div class="header">
				<p style="font-size: 20px;">Arabic/Persian to IJMES Transliterator (APIT) <span style="font-size:10px;margin-bottom:2px;">super duper alpha v0.1.20130620</span></p>
				<p style="font-size: 10px;"><a href="about.php">About</a>
			</div>
			<div class="form-div">
				<form action="index.php" method="get">
					<label id="label_q" for="q">Arabic/Persian text</label>
					<textarea name="q" rows="5" cols="10"><?php echo $q; ?></textarea>

					<p>
						<input type="radio" name="lang" value="Arabic" checked>Arabic
						<input type="radio" name="lang" value="Persian">Persian
					</p>

					<input type="submit" name="submit" value="Convert" />
				</form>
				<p>&nbsp;</p>
				<?php if($output != "") { ?>
				<div class="input">
					<label for="output">IJMES-converted text</label>
					<textarea name="output" rows="5" cols="10" readonly><?php print_r($output); ?></textarea>
				</div>
				<?php } ?>
			</div>
			<div class="footer"></div>
		</div>
	</body>
</html>	