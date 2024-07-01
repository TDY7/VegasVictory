<?php
$last_timestamp_file = "last_timestamp.txt";
$last_timestamp = file_exists($last_timestamp_file) ? file_get_contents($last_timestamp_file) : '0';
$file = "chat_log.txt";
$messages = [];
while (true) {
	if (file_exists($file) && filesize($file) > 0) {
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$last_line = end($lines);
		list($time, $message) = explode(" - ", $last_line, 2);
		list($pseudo, $color, $msg) = explode(":", $message, 3);

		if (strtotime($time) > strtotime($last_timestamp)) {
			$messages[] = $time . " - " . $pseudo . ":" . $color . ":" . $msg;
			file_put_contents($last_timestamp_file, $time);
			break;
		}
	}

	usleep(30000);
}

echo json_encode($messages);
?>