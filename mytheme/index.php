<?php
header('Access-Control-Allow-Origin: http://localhost:8000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');  // cache for 1 day
// 中国时间
date_default_timezone_set('Etc/GMT+12');
date_default_timezone_set('NZ');
// define('TIMEZONE', 'Etc/GMT+12');
// ini_set('date.timezone', 'Etc/GMT+12');
require 'public/index.php';
