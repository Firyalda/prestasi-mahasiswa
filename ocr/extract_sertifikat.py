from paddleocr import PaddleOCR
import sys
import json
import os
from pdf2image import convert_from_path

POPPLER_PATH = r'C:\poppler\bin'

def extract_from_pdf(pdf_path):
    try:
        # Konversi PDF ke gambar
        images = convert_from_path(
            pdf_path, 
            dpi=150,
            poppler_path=POPPLER_PATH,
            fmt='jpeg'
        )
        
        if not images:
            return {'success': False, 'error': 'No pages found'}
        
        # Inisialisasi OCR
        ocr = PaddleOCR(lang='ch', show_log=False)
        
        semua_hasil = []
        
        for i, img in enumerate(images):
            temp_path = f'temp_{i}.jpg'
            img.save(temp_path, 'JPEG', quality=95)
            
            result = ocr.ocr(temp_path)
            
            if result and result[0]:
                for line in result[0]:
                    semua_hasil.append({
                        'text': line[1][0],
                        'confidence': round(line[1][1], 4)
                    })
            
            os.remove(temp_path)
        
        if not semua_hasil:
            return {'success': False, 'error': 'No text detected'}
        
        # Hitung rata-rata akurasi
        avg_conf = sum([h['confidence'] for h in semua_hasil]) / len(semua_hasil)
        full_text = ' '.join([h['text'] for h in semua_hasil])
        
        # Tentukan status keaslian
        if avg_conf >= 0.85:
            status = 'asli'
        elif avg_conf >= 0.60:
            status = 'ragu'
        else:
            status = 'palsu'
        
        return {
            'success': True,
            'text': full_text,
            'avg_confidence': round(avg_conf, 4),
            'status': status
        }
        
    except Exception as e:
        return {'success': False, 'error': str(e)}

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'success': False, 'error': 'No file path provided'}))
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    result = extract_from_pdf(pdf_path)
    print(json.dumps(result))