import argparse
import os
import sys

def stamp_pdf_fitz(input_path, output_path, text):
    import pymupdf
    doc = pymupdf.open(input_path)
    if len(doc) == 0:
        raise ValueError("Input PDF has no pages")

    text = (text or "").strip()
    if not text:
        doc.save(output_path)
        doc.close()
        return

    target_page = doc[0]
    for p in doc:
        if p.search_for("IT APPROVAL SECTION") or p.search_for("IT APPROVAL"):
            target_page = p
            break

    it_matches = target_page.search_for("IT APPROVAL SECTION")
    if not it_matches:
        it_matches = target_page.search_for("IT APPROVAL")

    target_rc = None
    if it_matches:
        it_rect = it_matches[0]
        rc_matches = target_page.search_for("Review & Comments")
        for rc in rc_matches:
            if rc.y0 >= it_rect.y0 - 5 and rc.y0 <= it_rect.y1 + 80:
                target_rc = rc
                break

    if target_rc:
        box_y_top = target_rc.y1 + 3.0
        box_y_bottom = target_rc.y1 + 65.0
    elif it_matches:
        box_y_top = it_matches[0].y1 + 30.0
        box_y_bottom = box_y_top + 62.0
    else:
        # Standard fallback coordinates for EWTR template
        box_y_top = 576.0
        box_y_bottom = 638.0

    # Review & Comments horizontal margins:
    # Left divider line is at x = 195.3 pt -> safe start at 202.0 pt
    # Right border is at x = 551.5 pt -> safe end at 546.0 pt
    box_x_left = 202.0
    box_x_right = 546.0

    text_len = len(text)
    if text_len > 250:
        fontsize = 7.0
    elif text_len > 120:
        fontsize = 8.0
    else:
        fontsize = 9.0

    rect = pymupdf.Rect(box_x_left, box_y_top, box_x_right, box_y_bottom)
    target_page.insert_textbox(rect, text, fontsize=fontsize, fontname="helv", color=(0.12, 0.16, 0.23))

    out_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(out_dir, exist_ok=True)

    temp_output_path = output_path + ".tmp"
    doc.save(temp_output_path)
    doc.close()

    if os.path.exists(output_path):
        os.remove(output_path)
    os.replace(temp_output_path, output_path)

def stamp_pdf_reportlab(input_path, output_path, text):
    from io import BytesIO
    import pypdf
    from reportlab.lib.styles import ParagraphStyle
    from reportlab.pdfgen import canvas
    from reportlab.platypus import Frame, Paragraph

    reader = pypdf.PdfReader(input_path)
    total_pages = len(reader.pages)
    if total_pages == 0:
        raise ValueError("Input PDF has no pages")

    text = (text or "").strip()
    if not text:
        writer = pypdf.PdfWriter()
        for p in reader.pages:
            writer.add_page(p)
        with open(output_path, "wb") as f:
            writer.write(f)
        return

    target_page_idx = 0
    target_page = reader.pages[target_page_idx]
    page_w_pt = float(target_page.mediabox.width)
    page_h_pt = float(target_page.mediabox.height)

    # In EWTR layout, Review & Comments box:
    # Left at 202.0 pt, Right at 546.0 pt -> Width = 344.0 pt
    box_x_pt = 202.0
    box_w_pt = 344.0
    box_y_top_pt = 576.0
    box_h_pt = 62.0
    box_y_pt = page_h_pt - (box_y_top_pt + box_h_pt)

    text_len = len(text)
    if text_len > 250:
        font_size = 7.0
        leading = 9.0
    elif text_len > 120:
        font_size = 8.0
        leading = 10.0
    else:
        font_size = 9.0
        leading = 12.0

    packet = BytesIO()
    can = canvas.Canvas(packet, pagesize=(page_w_pt, page_h_pt))

    frame = Frame(
        box_x_pt,
        box_y_pt,
        box_w_pt,
        box_h_pt,
        topPadding=2,
        bottomPadding=2,
        leftPadding=2,
        rightPadding=2
    )

    style = ParagraphStyle(
        'EwtrComments',
        fontName='Helvetica',
        fontSize=font_size,
        leading=leading,
        textColor='#1e293b'
    )

    escaped_text = text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;').replace('\n', '<br/>')
    story = [Paragraph(escaped_text, style)]
    frame.addFromList(story, can)
    can.save()

    packet.seek(0)
    overlay_reader = pypdf.PdfReader(packet)
    writer = pypdf.PdfWriter()

    for idx, p in enumerate(reader.pages):
        if idx == target_page_idx:
            p.merge_page(overlay_reader.pages[0])
        writer.add_page(p)

    out_dir = os.path.dirname(os.path.abspath(output_path))
    os.makedirs(out_dir, exist_ok=True)

    temp_output_path = output_path + ".tmp"
    with open(temp_output_path, "wb") as f:
        writer.write(f)

    if os.path.exists(output_path):
        os.remove(output_path)
    os.replace(temp_output_path, output_path)

def stamp_pdf(input_path, output_path, text):
    if not os.path.exists(input_path):
        raise FileNotFoundError(f"Input file not found: {input_path}")

    try:
        stamp_pdf_fitz(input_path, output_path, text)
    except ImportError:
        stamp_pdf_reportlab(input_path, output_path, text)

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
