<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/data.json';
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

