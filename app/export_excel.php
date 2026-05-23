<?php
declare(strict_types=1);

// Export Excel tanpa library: pakai SpreadsheetML (Excel bisa buka file .xls)
// Untuk kompatibilitas, kita output tabel gaya Excel 2003.

$file = __DIR__ . '/data.json';
$data = [];
if (file_exists($file)) {
  $raw = file_get_contents($file);
  $tmp = json_decode($raw, true);
  if (is_array($tmp)) $data = $tmp;
}

$filename = 'Data_Teknisi_Bulanan_' . date('Y-m-d_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

function cell($v): string {
  $s = (string)$v;
  $s = str_replace(["\t","\r","\n"], ' ', $s);
  $s = htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  return '<Cell><Data ss:Type="String">' . $s . '</Data></Cell>';
}

// Mata uang: $data['harga'] float
function priceCell($v): string {
  if ($v === null || $v === '') {
    return '<Cell><Data ss:Type="Number">0</Data></Cell>';
  }
  $num = is_numeric($v) ? (float)$v : 0;
  return '<Cell><Data ss:Type="Number">' . $num . '</Data></Cell>';
}

$rows = '';
$no = 1;
foreach (array_reverse($data) as $row) {
  $rows .= '<Row>';
  $rows .= '<Cell><Data ss:Type="Number">' . $no . '</Data></Cell>';
  $rows .= cell($row['nama_teknisi'] ?? '');
  $rows .= cell($row['nama_customer'] ?? '');
  $rows .= cell($row['alamat'] ?? '');
  $rows .= cell($row['titik_lokasi'] ?? '');;
  $rows .= priceCell($row['harga'] ?? ($row['harga_disp'] ?? ''));
  $rows .= cell($row['partner'] ?? '');
  $rows .= cell($row['proyek'] ?? '');
  $rows .= cell($row['kegiatan'] ?? '');
  $rows .= '</Row>';
  $no++;
}

echo '<?xml version="1.0"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:o="urn:schemas-microsoft-com:office:office"
  xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:html="http://www.w3.org/TR/REC-html40">
  <Worksheet ss:Name="Data Teknisi Bulanan">
    <Table>
      <Row>
        <Cell><Data ss:Type="String">No</Data></Cell>
        <Cell><Data ss:Type="String">Nama Teknisi</Data></Cell>
        <Cell><Data ss:Type="String">Nama Customer</Data></Cell>
        <Cell><Data ss:Type="String">Alamat</Data></Cell>
        <Cell><Data ss:Type="String">Titik Lokasi</Data></Cell>
        <Cell><Data ss:Type="String">Harga</Data></Cell>
        <Cell><Data ss:Type="String">Partner</Data></Cell>
        <Cell><Data ss:Type="String">Proyek</Data></Cell>
        <Cell><Data ss:Type="String">Kegiatan</Data></Cell>
      </Row>
      <?php echo $rows; ?>
    </Table>
  </Worksheet>
</Workbook>

