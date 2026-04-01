<?php
$config = parse_ini_file('/var/www/config.ini');

define('MAIL_ENABLED', true);
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'pulse.booking.demo@gmail.com');
define('MAIL_PASSWORD', $config['mail_pass']);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'pulse.booking.demo@gmail.com');
define('MAIL_FROM_NAME', 'PULSE');
?>