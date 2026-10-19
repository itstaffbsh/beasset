import re

filepath = 'routes/api.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

old_route = "Route::apiResource('products', AsetController::class);"
new_route = "Route::apiResource('products', AsetController::class);\n        Route::get('/products/{product}/qr', [AsetController::class, 'printQr']);"

content = content.replace(old_route, new_route)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Added qr route to api.php")
