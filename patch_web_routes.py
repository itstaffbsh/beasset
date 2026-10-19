import re

filepath = 'routes/web.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r"Route::get\('/api/scan/\{token\}',\s*\[ScanController::class,\s*'apiShow'\]\)->name\('api\.scan\.show'\);\n", "", content)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Removed conflicting /api/scan route from web.php!")
