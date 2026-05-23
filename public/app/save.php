<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
if (!$raw) {
  http_response_code(400);
  echo json_encode(['error' => 'Body kosong']);
  exit;
}

$payload = json_decode($raw, true);
if (!is_array($payload)) {
  http_response_code(400);
  echo json_encode(['error' => 'JSON tidak valid']);
  exit;
}

$required = ['titik_lokasi','harga','partner','proyek','kegiatan'];
foreach ($required as $k) {
  if (!isset($payload[$k]) || $payload[$k] === '' || $payload[$k] === null) {
    http_response_code(422);
    echo json_encode(['error' => "Field wajib tidak lengkap: {$k}"]);
    exit;
  }
}

function clean_str($v): string {
  return trim((string)$v);
}

$dataRow = [
  'nama_teknisi' => isset($payload['nama_teknisi']) ? clean_str($payload['nama_teknisi']) : '',
  'nama_customer' => isset($payload['nama_customer']) ? clean_str($payload['nama_customer']) : '',
  'alamat' => isset($payload['alamat']) ? clean_str($payload['alamat']) : '',
  'titik_lokasi' => clean_str($payload['titik_lokasi']),
  'harga_disp' => (string)$payload['harga'],
  'harga' => is_numeric($payload['harga']) ? (float)$payload['harga'] : null,
  'partner' => clean_str($payload['partner']),
  'proyek' => clean_str($payload['proyek']),
  'kegiatan' => clean_str($payload['kegiatan']),
  'created_at' => date('c'),
];

if ($dataRow['harga'] === null) {
  http_response_code(422);
  echo json_encode(['error' => 'Harga tidak valid']);
  exit;
}

$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) {
  @mkdir($storageDir, 0777, true);
}

$file = $storageDir . '/data.json';
if (!file_exists($file)) {
  @file_put_contents($file, '[]');
}

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

rewind($fp);
$existingRaw = stream_get_contents($fp);
$existing = [];
if ($existingRaw) {
  $tmp = json_decode($existingRaw, true);
  if (is_array($tmp)) $existing = $tmp;
}

$existing[] = $dataRow;

ftruncate($fp, 0);
rewind($fp);
$ok = fwrite($fp, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

if ($ok === false) {
  http_response_code(500);
  echo json_encode(['error' => 'Gagal menyimpan']);
  exit;
}

echo json_encode(['success' => true]);

