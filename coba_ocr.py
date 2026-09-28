from paddleocr import PaddleOCR
import cv2
import numpy as np
from PIL import Image, ImageDraw

print("=" * 40)
print("TEST PADDLEOCR")
print("=" * 40)

# Buat gambar test sederhana
img = Image.new('RGB', (500, 150), color='white')
draw = ImageDraw.Draw(img)
draw.text((50, 60), "HALO INI TEST OCR", fill='black')
img.save('test_ocr.jpg')

# Inisialisasi OCR TANPA parameter use_angle_cls
ocr = PaddleOCR(lang='ch')  # <-- Hapus use_angle_cls

# Baca gambar dengan opencv
image = cv2.imread('test_ocr.jpg')

# OCR
result = ocr.ocr(image)

print("\nHASIL OCR:")
if result and result[0]:
    for line in result[0]:
        print(line[1][0])
else:
    print("Tidak ada teks")