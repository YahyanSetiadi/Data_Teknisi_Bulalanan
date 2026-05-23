<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
  http_response_code(500);
  echo json_encode([]);
  exit;
}
$file = $storageDir . '/data.json';
if (!file_exists($file)) {
  echo json_encode([]);
  exit;
}

$raw = file_get_contents($file);

$data = [];
if ($raw) {
  $tmp = json_decode($raw, true);
  if (is_array($tmp)) $data = $tmp;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);

