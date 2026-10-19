import re

filepath = 'app/Http/Controllers/AsetController.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('QrCode::margin(1)->size(300)->generate($url);', 'QrCode::size(300)->generate($url);')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Removed margin(1) from QrCode!")
