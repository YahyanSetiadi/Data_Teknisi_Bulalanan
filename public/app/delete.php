<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Endpoint reset/hapus semua data (tanpa database)
// Body opsional: {"confirm": true}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method tidak diizinkan']);
  exit;
}

$raw = file_get_contents('php://input');
$payload = null;
if ($raw) {
  $payload = json_decode($raw, true);
}

if (is_array($payload) && array_key_exists('confirm', $payload)) {
  if (!$payload['confirm']) {
    http_response_code(422);
    echo json_encode(['error' => 'Konfirmasi tidak valid']);
    exit;
  }
}

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir)) {
  @mkdir($storageDir, 0777, true);
}

$file = $storageDir . '/data.json';

$fp = fopen($file, 'c+');
if (!$fp) {
  http_response_code(500);
  echo json_encode(['error' => 'Tidak bisa membuka data.json']);
  exit;
}

if (!flock($fp, LOCK_EX)) {
  http_response_code(500);
  echo json_encode(['error' => 'Tidak bisa mengunci file']);
  fclose($fp);
  exit;
}

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, '[]');
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['success' => true]);

