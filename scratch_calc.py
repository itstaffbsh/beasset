import pypdf

reader = pypdf.PdfReader('storage/app/public/ewtrs/ewtr_1792383169_yI7vmFKK.pdf')
page = reader.pages[0]

def mult_matrix(m1, m2):
    # 3x3 affine matrix multiplication: [a, b, 0], [c, d, 0], [e, f, 1]
    a1, b1, c1, d1, e1, f1 = m1
    a2, b2, c2, d2, e2, f2 = m2
    return [
        a1*a2 + b1*c2,
        a1*b2 + b1*d2,
        c1*a2 + d1*c2,
        c1*b2 + d1*d2,
        e1*a2 + f1*c2 + e2,
        e1*b2 + f1*d2 + f2
    ]

results = []
def visitor(text, cm, tm, fontDict, fontSize):
    t = text.strip()
    if t in ['IT APPROVAL SECTION', 'IT Approval', 'Review & Comments', 'Pande Gede Prana Jaya', 'Date', 'Management Approval']:
        # Point in text space is (0, 0)
        # Transformed by TM then CM:
        # User space point is (TM[4]*CM[0] + TM[5]*CM[2] + CM[4], TM[4]*CM[1] + TM[5]*CM[3] + CM[5])
        # Wait, standard affine: [x', y', 1] = [x, y, 1] * TM * CM
        # Since [0, 0, 1] * TM = [TM[4], TM[5], 1]
        x_tm = tm[4]
        y_tm = tm[5]
        # Then multiply by CM:
        x_user = x_tm * cm[0] + y_tm * cm[2] + cm[4]
        y_user = x_tm * cm[1] + y_tm * cm[3] + cm[5]
        
        # In FPDF, origin is at TOP-LEFT of page!
        # Page height is page.mediabox.height (792 pt = 279.4 mm for Letter, or 842 pt = 297 mm for A4)
        page_h = float(page.mediabox.height)
        
        # User space in points (from bottom-left):
        # FPDF y from top: y_fpdf_pt = page_h - y_user
        # In mm (FPDF default unit is mm, 1 pt = 25.4 / 72 mm = 0.352778 mm):
        x_mm = x_user * (25.4 / 72.0)
        y_mm_bottom = y_user * (25.4 / 72.0)
        y_mm_from_top = (page_h - y_user) * (25.4 / 72.0)
        
        results.append({
            'text': t,
            'x_pt': x_user,
            'y_pt': y_user,
            'x_mm': x_mm,
            'y_mm_from_top': y_mm_from_top,
            'y_mm_bottom': y_mm_bottom
        })

page.extract_text(visitor_text=visitor)

for r in results:
    print(f"'{r['text']}': x={r['x_mm']:.2f}mm, y_from_top={r['y_mm_from_top']:.2f}mm (pt: x={r['x_pt']:.1f}, y_bottom={r['y_pt']:.1f})")
