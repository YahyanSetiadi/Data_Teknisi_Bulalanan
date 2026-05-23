// endpoints ada di dalam public/app agar tidak 404 di Render
const API_SAVE = "./app/save.php";
const API_LIST = "./app/list.php";
const API_EXPORT = "./app/export_excel.php";

function formatError(res) {
  return `${res.status} ${res.statusText}`;
}

const form = document.getElementById("formTambah");
const statusEl = document.getElementById("status");
const tbody = document.getElementById("tbody");
const btnExport = document.getElementById("btnExport");

function setStatus(msg, type) {
  statusEl.textContent = msg || "";
  statusEl.classList.remove("ok", "err");
  if (type) statusEl.classList.add(type);
}

function moneyToNumber(value) {
  // allow: 250000 or 250.000 or 250.000,50
  if (value == null) return NaN;
  const s = String(value).trim().replace(/\s/g, "");
  if (s === "") return NaN;
  // remove thousands separators (.) and convert comma to dot
  const normalized = s.replace(/\./g, "").replace(",", ".");
  return Number(normalized);
}

async function loadList() {
  try {
    const res = await fetch(API_LIST, { method: "GET" });
    if (!res.ok) throw new Error("Gagal memuat data");
    const data = await res.json();
    renderTable(Array.isArray(data) ? data : []);
  } catch (e) {
    const msg = e && e.message ? e.message : String(e);
    setStatus("Tidak bisa memuat data. " + msg, "err");
  }
}

function escHtml(str) {
  return String(str ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "<")
    .replaceAll(">", ">")
    .replaceAll('"', '"')
    .replaceAll("'", "&#039;");
}

function renderTable(items) {
  tbody.innerHTML = "";
  items
    .slice()
    .reverse() // tampilkan yang terbaru di bawah/atas? (dibalik agar yang baru terlihat)
    .forEach((row, idx) => {
      const tr = document.createElement("tr");
      const no = items.length - idx;
      tr.innerHTML = `
        <td>${no}</td>
        <td>${escHtml(row.nama_teknisi)}</td>
        <td>${escHtml(row.nama_customer)}</td>
        <td>${escHtml(row.titik_lokasi)}</td>
        <td>${escHtml(row.harga_disp ?? row.harga ?? "")}</td>

        <td>${escHtml(row.partner)}</td>
        <td>${escHtml(row.proyek)}</td>
        <td>${escHtml(row.kegiatan)}</td>
      `;
      tbody.appendChild(tr);
    });
}

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const fd = new FormData(form);
  const hargaNum = moneyToNumber(fd.get("harga"));

  if (Number.isNaN(hargaNum)) {
    setStatus("Harga tidak valid. Contoh: 250000", "err");
    return;
  }

  const payload = {
    nama_teknisi: (fd.get("nama_teknisi") ?? "").toString().trim(),
    nama_customer: (fd.get("nama_customer") ?? "").toString().trim(),
    alamat: (fd.get("alamat") ?? "").toString().trim(),
    titik_lokasi: (fd.get("titik_lokasi") ?? "").toString().trim(),
    harga: hargaNum,
    partner: (fd.get("partner") ?? "").toString().trim(),
    proyek: (fd.get("proyek") ?? "").toString().trim(),
    kegiatan: (fd.get("kegiatan") ?? "").toString().trim(),
  };

  // validasi required
  for (const key of [
    "titik_lokasi",
    "harga",
    "partner",
    "proyek",
    "kegiatan",
  ]) {
    if (payload[key] === "" || payload[key] == null) {
      setStatus("Field wajib belum diisi.", "err");
      return;
    }
  }

  try {
    setStatus("Menyimpan...", "");
    const res = await fetch(API_SAVE, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const out = await res.json().catch(() => null);
    if (!res.ok) {
      const msg = out?.error || "Gagal menyimpan";
      setStatus(msg, "err");
      return;
    }

    setStatus("Data tersimpan.", "ok");
    form.reset();
    await loadList();
  } catch (err) {
    setStatus("Gagal konek ke server.", "err");
  }
});

btnExport.addEventListener("click", async () => {
  // export otomatis: browser akan download file dari PHP
  window.location.href = API_EXPORT;
});

loadList();
