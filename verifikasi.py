import sys
import pytesseract
from PIL import Image
import os
from pdf2image import convert_from_path

poppler_path = r'C:\poppler\bin'
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

kata_kunci = ['sertifikat', 'juara', 'penghargaan', 'diberikan', 'kepada', 'sebagai', 'lomba', 'kompetisi', 'penyelenggara']

if len(sys.argv) < 2:
    print("Tidak ada file")
    sys.exit(1)

file_path = sys.argv[1]

print("=" * 60)
print("VERIFIKASI KEASLIAN DATA PENDUKUNG SERTIFIKAT")
print("=" * 60)
print()

nama_file = os.path.basename(file_path)
print("[FILE] Memproses: " + nama_file)

# CEK APAKAH FILE PDF
if file_path.lower().endswith('.pdf'):
    print("[INFO] File PDF terdeteksi, mengkonversi...")
    images = convert_from_path(file_path, dpi=150, poppler_path=poppler_path)
    teks = ""
    for img in images:
        teks += pytesseract.image_to_string(img, lang='ind')
else:
    print("[INFO] File Gambar terdeteksi...")
    gambar = Image.open(file_path)
    teks = pytesseract.image_to_string(gambar, lang='ind')

print("   [TEKS] HASIL OCR:")
print("   " + "-" * 40)
for baris in teks.split('\n'):
    if baris.strip():
        print(f"      {baris}")
print("   " + "-" * 40)

teks_lower = teks.lower()
kata_ditemukan = [k for k in kata_kunci if k in teks_lower]

if kata_ditemukan:
    print("   [STATUS] ASLI (Data Pendukung Valid)")
    print("   [KATA] Ditemukan: " + ', '.join(kata_ditemukan))
else:
    print("   [STATUS] DIDUGA PALSU (Data Pendukung Tidak Valid)")

print()
print("=" * 60)
print("[SELESAI] PROSES SELESAI!")
print("=" * 60)