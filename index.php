<?php
use Werd\Ivona\SpeechCloud;
use Werd\Ivona\Models\Input;
use Werd\Ivona\Models\OutputFormat;
use Werd\Ivona\Models\Parameters;
use Werd\Ivona\Models\Voice;

require_once __DIR__ . '/plugins/autoload.php';
require_once __DIR__ . '/config.php';

if (file_exists(__DIR__ . '/temp') === false) {
	if (mkdir(__DIR__ . '/temp') === false) {
		die('Nie mogę stworzyć katalogu `temp`.');
	}
}
if (file_exists(__DIR__ . '/output') === false) {
	if (mkdir(__DIR__ . '/output') === false) {
		die('Nie mogę stworzyć katalogu `output`.');
	}
}

$history = [];
if (file_exists(__DIR__ . '/output/history.json')) {
	$history = json_decode(file_get_contents(__DIR__ . '/output/history.json'), true);
}

$requestRoot = strtr($_SERVER['REQUEST_URI'], [$_SERVER['SCRIPT_NAME'] => '']);
$bootstrapPath = $requestRoot . 'plugins/twbs/bootstrap/dist/';
if (array_key_exists('form', $_POST) && (int) $_POST['form'] === 1) {
	if (
		array_key_exists('quote', $_POST)
		&& strlen($_POST['quote']) > 0
		&& strlen($_POST['quote']) < 50)
	{
		$quote = trim($_POST['quote']);
		$filename = md5($quote) . '.mp3';
		$speechCloud = new SpeechCloud([
			'access_key' => IVONA_API_ACCESS_KEY,
			'secret_key' => IVONA_API_SECRET_KEY,
			'region' => IVONA_API_REGION
		]);
		$data = $speechCloud->createSpeech(new Input([
			Input::DATA => $quote
		]), new OutputFormat(), new Parameters(), new Voice([
			Voice::NAME => 'Jacek',
			Voice::LANGUAGE => 'pl-PL'
		]));
		$tempFile = __DIR__ . '/temp/' . $filename;
		$outputFile = __DIR__ . '/output/' . $filename;
		file_put_contents($outputFile, $data);
		if (file_exists(SOX_PATH) && is_executable(SOX_PATH)) {
			exec('"' . SOX_PATH . '" "' . $outputFile . '" "' . $outputFile . '" pad 0 1');
			exec('"' . SOX_PATH . '" "' . $outputFile . '" "' . $tempFile . '" tempo 0.5');
			exec('"' . SOX_PATH . '" "' . $outputFile . '" "' . $outputFile . '" "' . $tempFile . '" "' . $outputFile . '"');
			unlink($tempFile);
		}
		array_push($history, [
			'quote' => $quote,
			'filename' => $filename,
			'timestamp' => time()
		]);
		file_put_contents(file_get_contents(__DIR__ . '/output/history.json', json_encode($history)));
	}
}
if (array_key_exists('output', $_GET) && strlen($_GET['output'])) {
	if (file_exists(__DIR__ . '/output/' . $_GET['output'])) {
		header('Content-Type: audio/mpeg');
		echo file_get_contents(__DIR__ . '/output/' . $_GET['output']);
		exit;
	}
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>Mp3Quotes</title>
	<link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,300,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<link href="<?php echo $bootstrapPath ?>css/bootstrap.min.css" rel="stylesheet">
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<style type="text/css">
		body {
			font-family: 'Roboto Slab', serif;
		}
	</style>
</head>
<body>

<div class="container">
	<div class="row">
		<div class="col-xs-12 text-center">
			<h1><span class="text-danger">Mp3</span>Quotes</h1>
			<p class="lead">Twórz audio cytat swoich współpracowników!</p>
		</div>
	</div>
	<div clas="row">
		<div class="col-xs-12">
			<form method="post">
				<div class="form-group">
					<div class="input-group">
						<input type="text" maxlength="50" class="form-control input-lg" name="quote" placeholder="Wpisz cytat kolegi...">
						<span class="input-group-btn">
							<input type="submit" class="btn btn-success btn-lg" value="Go!">
						</span>
					</div>
				</div>
				<input type="hidden" name="form" value="1">
			</form>
		</div>
	</div>
	<?php
	if (isset($filename)) {
		$audioPath = $requestRoot . '?output=' . $filename;
		?><div clas="row"><div class="col-xs-12 text-center">
			<p>
				<a href="<?php echo $audioPath ?>" target="_blank"><?php echo $filename ?></a><br>
				<audio controls autoplay loop>
					<source src="<?php echo $audioPath ?>" type="audio/mpeg">
				</audio>
			</p>
		</div></div><?php
	}
	if (count($history)) {
		echo '<div class="row"><div class="col-xs-12"><ul class="list-unstyled">';
		foreach (array_reverse($history) as $record) {
			$audioPath = $requestRoot . '?output=' . $record['filename'];
			echo '<li>' . htmlspecialchars($record['quote']) . ' <span class="text-mute">'
				. date('d-m-Y H:i:s', $record['timestamp']) . '</span> '
				. '<a href="' . $audioPath . '" target="_blank"><i class="glyphicon glyphicon-play"></i></a></li>';
		}
		echo '</ul></div></div>';
	}
	?>
</div>
	
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="<?php echo $bootstrapPath ?>js/bootstrap.min.js"></script>
</body>
</html>