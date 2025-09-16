<?php
$h=getenv('DB_HOST'); $p=getenv('DB_PORT'); $d=getenv('DB_DATABASE');
$u=getenv('DB_USERNAME'); $pw=getenv('DB_PASSWORD');
echo "Trying $h:$p/$d\n";
try {
  new PDO("mysql:host=$h;port=$p;dbname=$d;charset=utf8mb4",$u,$pw,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  echo "OK\n";
} catch (Exception $e) { echo $e->getMessage()."\n"; }
