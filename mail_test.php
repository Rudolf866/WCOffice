<?php

echo 'We start test!!!!';

if (isset($_GET['test'])) {
	fastcgi_finish_request();
	mail('hukutuh.ahtoh@yandex.ru', 'Test mail', 'test message');
	echo 'finish!';
}

?>