import pypdf

reader = pypdf.PdfReader('storage/app/public/ewtrs/ewtr_1792383169_yI7vmFKK.pdf')
page = reader.pages[0]

# Let's inspect all text and drawings
print("Page dimensions: width =", float(page.mediabox.width) * 25.4/72, "mm, height =", float(page.mediabox.height) * 25.4/72, "mm")

# Let's find any text in the left column
left_texts = []
def visitor(text, cm, tm, fontDict, fontSize):
    t = text.strip()
    if t:
        x_tm = tm[4]
        y_tm = tm[5]
        x_user = x_tm * cm[0] + y_tm * cm[2] + cm[4]
        y_user = x_tm * cm[1] + y_tm * cm[3] + cm[5]
        page_h = float(page.mediabox.height)
        x_mm = x_user * (25.4 / 72.0)
        y_from_top = (page_h - y_user) * (25.4 / 72.0)
        if 190 <= y_from_top <= 235:
            left_texts.append((t, x_mm, y_from_top))

page.extract_text(visitor_text=visitor)
left_texts.sort(key=lambda item: (item[2], item[1]))
for t in left_texts:
    print(f"y={t[2]:.2f}mm, x={t[1]:.2f}mm: '{t[0]}'")
