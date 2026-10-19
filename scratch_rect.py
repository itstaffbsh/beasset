import pypdf

pdf_path = 'storage/app/public/ewtrs/ewtr_1792383169_yI7vmFKK.pdf'
reader = pypdf.PdfReader(pdf_path)
page = reader.pages[0]

print('mediabox:', page.mediabox)
print('cropbox:', page.cropbox)
print('rotation:', page.rotation)

# Let's inspect drawing operators / lines in the page stream
# to find the exact boundaries of the table!
from pypdf.pdf import ContentStream

cs = ContentStream(page['/Contents'], reader)
rects = []
for operands, operator in cs.operations:
    if operator == b're':
        rects.append(operands)

print(f'Total rectangles found: {len(rects)}')
for r in rects:
    # operands: [x, y, w, h]
    try:
        x, y, w, h = [float(v) for v in r]
        # check if it's in the IT approval section
        if 500 <= y <= 900 or -900 <= y <= -500 or 100 <= w <= 600:
            print(f'Rect: x={x}, y={y}, w={w}, h={h}')
    except Exception as e:
        pass
