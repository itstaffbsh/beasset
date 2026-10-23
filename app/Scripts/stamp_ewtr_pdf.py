import argparse
import os
import sys
from io import BytesIO

# Auto install pypdf & reportlab if missing
try:
    import pypdf
    import reportlab
except ImportError:
    import subprocess
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "pypdf", "reportlab", "--break-system-packages", "--quiet", "--no-warn-script-location"])
        import pypdf
        import reportlab
    except Exception:
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", "pypdf", "reportlab", "--quiet", "--no-warn-script-location"])
            import pypdf
            import reportlab
        except Exception as e:
            sys.stderr.write(f"ERROR: Missing pypdf or reportlab, could not auto-install: {e}\n")
            sys.exit(1)

from reportlab.lib.styles import ParagraphStyle
from reportlab.pdfgen import canvas
from reportlab.platypus import Frame, Paragraph

def mult(m1, m2):
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

def stamp_pdf(input_path, output_path, text):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    reader = pypdf.PdfReader(input_path)
    total_pages = len(reader.pages)
    if total_pages == 0:
        raise ValueError("Input PDF has no pages")

    text = (text or "").strip()
    if not text:
        # Just copy input to output
        writer = pypdf.PdfWriter()
        for p in reader.pages:
            writer.add_page(p)
        with open(output_path, "wb") as f:
            writer.write(f)
        return

    # Find target page and coordinates
    target_page_idx = 0
    detected_y_top_mm = None

    for idx, page in enumerate(reader.pages):
        found_tokens = []
        try:
            def visitor(t, cm, tm, font_dict, font_size):
                if not t.strip():
                    return
                upper = t.upper()
                if "IT APPROVAL SECTION" in upper or "IT APPROVAL" in upper:
                    m = mult(tm, cm)
                    y_pt = m[5]
                    h_pt = float(page.mediabox.height)
                    y_top_mm = (h_pt - y_pt) * 25.4 / 72.0
                    found_tokens.append(y_top_mm)

            page.extract_text(visitor_text=visitor)
            if found_tokens:
                target_page_idx = idx
                detected_y_top_mm = min(found_tokens)
                break
        except Exception:
            pass

    target_page = reader.pages[target_page_idx]
    page_w_pt = float(target_page.mediabox.width)
    page_h_pt = float(target_page.mediabox.height)
    page_h_mm = page_h_pt * 25.4 / 72.0

    # Review & Comments box geometry:
    # Calibrated baseline:
    # Divider is at x = 60.7 mm -> box_x starts at 63.5 mm (~180 pt)
    # Right border is at x = 196.6 mm -> width is 130 mm (~368.5 pt)
    box_x_pt = 63.5 * 72.0 / 25.4
    box_w_pt = 130.0 * 72.0 / 25.4

    if detected_y_top_mm is not None:
        # In EWTR layout, box content starts ~13.5 mm below "IT APPROVAL SECTION"
        box_y_top_mm = detected_y_top_mm + 13.5
        box_h_mm = 21.0
    else:
        # Standard fallback for US Letter page
        box_y_top_mm = 204.0
        box_h_mm = 21.0

    # In ReportLab, Y is measured from bottom of page
    box_y_bottom_mm = page_h_mm - (box_y_top_mm + box_h_mm)
    box_y_pt = box_y_bottom_mm * 72.0 / 25.4
    box_h_pt = box_h_mm * 72.0 / 25.4

    # Determine font size based on text length to avoid overflowing
    text_len = len(text)
    if text_len > 250:
        font_size = 7.0
        leading = 9.0
    elif text_len > 150:
        font_size = 7.5
        leading = 10.0
    else:
        font_size = 8.5
        leading = 11.0

    # Create ReportLab overlay
    packet = BytesIO()
    can = canvas.Canvas(packet, pagesize=(page_w_pt, page_h_pt))

    frame = Frame(
        box_x_pt,
        box_y_pt,
        box_w_pt,
        box_h_pt,
        topPadding=0,
        bottomPadding=0,
        leftPadding=0,
        rightPadding=0
    )

    style = ParagraphStyle(
        'EwtrComments',
        fontName='Helvetica',
        fontSize=font_size,
        leading=leading,
        textColor='#1e293b'
    )

    # Format text into safe HTML for ReportLab Paragraph
    escaped_text = text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;').replace('\n', '<br/>')
    story = [Paragraph(escaped_text, style)]
    frame.addFromList(story, can)
    can.save()

    # Merge overlay with original PDF
    packet.seek(0)
    overlay_reader = pypdf.PdfReader(packet)
    writer = pypdf.PdfWriter()

    for idx, p in enumerate(reader.pages):
        if idx == target_page_idx:
            p.merge_page(overlay_reader.pages[0])
        writer.add_page(p)

    # Ensure output directory exists
    out_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(out_dir, exist_ok=True)

    # Use a temp file in the same directory to avoid issues if input_path == output_path
    temp_output_path = output_path + ".tmp"
    with open(temp_output_path, "wb") as f:
        writer.write(f)

    if os.path.exists(output_path):
        os.remove(output_path)
    os.replace(temp_output_path, output_path)

def main():
    parser = argparse.ArgumentParser(description="Stamp EWTR keterangan into PDF IT Approval Review & Comments box")
    parser.add_argument("--input", required=True, help="Input PDF path")
    parser.add_argument("--output", required=True, help="Output PDF path")
    parser.add_argument("--text", default="", help="Text to stamp")
    parser.add_argument("--text-file", default=None, help="Path to text file containing text to stamp")

    args = parser.parse_args()

    content = args.text
    if args.text_file and os.path.exists(args.text_file):
        with open(args.text_file, "r", encoding="utf-8") as f:
            content = f.read()

    stamp_pdf(args.input, args.output, content)
    print("SUCCESS")

if __name__ == "__main__":
    main()
