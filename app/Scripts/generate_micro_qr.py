import argparse
import json
import sys
import segno

def generate_micro_qr(content: str, with_border_frame: bool = True):
    content = (content or "").strip()
    if not content:
        raise ValueError("Content cannot be empty")

    is_micro = True
    try:
        # Try Micro QR with error correction 'm' or 'l'
        try:
            qr = segno.make_micro(content, error='m')
        except segno.DataOverflowError:
            qr = segno.make_micro(content, error='l')
    except (segno.DataOverflowError, Exception):
        # Fallback to compact standard QR code if content is too long for Micro QR
        qr = segno.make(content, error='m', micro=False)
        is_micro = False

    matrix = qr.matrix  # list of lists of 0/1
    mod_count = len(matrix)
    quiet_zone = 2 if is_micro else 3
    total_modules = mod_count + quiet_zone * 2

    svg_parts = []
    svg_parts.append(
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {total_modules} {total_modules}" '
        f'shape-rendering="crispEdges" width="100%" height="100%">'
    )
    # White background
    svg_parts.append(f'<rect width="{total_modules}" height="{total_modules}" fill="#ffffff"/>')

    # Outer border frame matching reference image
    if with_border_frame:
        border_stroke = 0.25 if is_micro else 0.4
        inset = border_stroke / 2.0
        svg_parts.append(
            f'<rect x="{inset:.2f}" y="{inset:.2f}" width="{total_modules - border_stroke:.2f}" '
            f'height="{total_modules - border_stroke:.2f}" fill="none" stroke="#000000" stroke-width="{border_stroke:.2f}"/>'
        )

    # Black modules
    # Group consecutive horizontal black modules into paths for compact SVG
    for r, row in enumerate(matrix):
        y = r + quiet_zone
        start_c = None
        for c, val in enumerate(row):
            if val:
                if start_c is None:
                    start_c = c
            else:
                if start_c is not None:
                    x = start_c + quiet_zone
                    w = c - start_c
                    svg_parts.append(f'<rect x="{x}" y="{y}" width="{w}" height="1" fill="#000000"/>')
                    start_c = None
        if start_c is not None:
            x = start_c + quiet_zone
            w = len(row) - start_c
            svg_parts.append(f'<rect x="{x}" y="{y}" width="{w}" height="1" fill="#000000"/>')

    svg_parts.append('</svg>')
    svg_string = ''.join(svg_parts)

    return {
        "is_micro": is_micro,
        "version": qr.version,
        "designator": getattr(qr, 'designator', str(qr.version)),
        "modules": mod_count,
        "total_modules": total_modules,
        "svg": svg_string,
    }

def main():
    parser = argparse.ArgumentParser(description="Generate Micro QR Code SVG")
    parser.add_argument("--content", required=True, help="Data to encode")
    parser.add_argument("--no-frame", action="store_true", help="Omit outer square border frame")
    parser.add_argument("--output", help="Optional path to write SVG output directly")

    args = parser.parse_args()
    try:
        result = generate_micro_qr(args.content, with_border_frame=not args.no_frame)
        if args.output:
            with open(args.output, "w", encoding="utf-8") as f:
                f.write(result["svg"])
        print(json.dumps(result))
    except Exception as e:
        sys.stderr.write(f"Error: {e}\n")
        sys.exit(1)

if __name__ == "__main__":
    main()
