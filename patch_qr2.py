import re

filepath = 'app/Http/Controllers/AsetController.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old_code = """        // Return Base64 of PNG
        $qrCode = QrCode::format('png')->margin(1)->size(300)->generate($url);
        return response()->json([
            'url' => $url,
            'qr_svg' => base64_encode($qrCode)
        ]);"""
        
new_code = """        // Return Base64 of SVG
        $qrCode = QrCode::margin(1)->size(300)->generate($url);
        return response()->json([
            'url' => $url,
            'qr_svg' => base64_encode($qrCode)
        ]);"""

content = content.replace(old_code, new_code)
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Reverted AsetController to SVG!")
