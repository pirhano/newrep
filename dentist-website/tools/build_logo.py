"""Generate the Dr Rhomija Nabil logo set as SVG (text outlined) + PNG exports.

Usage: python3 build_logo.py <montserrat-600.ttf> <montserrat-700.ttf> <out_dir>
"""
import os
import sys

import uharfbuzz as hb
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.ttLib import TTFont

BLUE = "#1C6DB5"
TEAL = "#1FA39A"
INK = "#14212E"
WHITE = "#FFFFFF"


class Face:
    def __init__(self, path):
        self.path = path
        self.tt = TTFont(path)
        self.upem = self.tt["head"].unitsPerEm
        blob = hb.Blob.from_file_path(path)
        self.hbfont = hb.Font(hb.Face(blob))
        self.glyphs = self.tt.getGlyphSet()
        self.order = self.tt.getGlyphOrder()

    def run(self, text, size, x, y, tracking=0.0):
        """Return (svg path d, advance width) for text with baseline at y."""
        buf = hb.Buffer()
        buf.add_str(text)
        buf.guess_segment_properties()
        hb.shape(self.hbfont, buf, {"kern": True, "liga": False})
        scale = size / self.upem
        track = tracking * size
        pen = SVGPathPen(self.glyphs)
        cx = 0.0
        for info, pos in zip(buf.glyph_infos, buf.glyph_positions):
            name = self.order[info.codepoint]
            tx = x + (cx + pos.x_offset) * scale
            ty = y - pos.y_offset * scale
            tp = TransformPen(pen, (scale, 0, 0, -scale, tx, ty))
            self.glyphs[name].draw(tp)
            cx += pos.x_advance + track / scale
        width = cx * scale - track  # no trailing tracking
        return pen.getCommands(), width


TOOTH = (
    "M50 31.5C44.5 25.5 34.5 23.2 30.2 30.4C26.4 36.8 28.2 46.6 31.6 54.4"
    "C33.6 59.2 35 65.6 36.4 72.4C37.4 77.6 41.8 78.6 43.4 73.6L46.2 63.2"
    "C47.2 59 52.8 59 53.8 63.2L56.6 73.6C58.2 78.6 62.6 77.6 63.6 72.4"
    "C65 65.6 66.4 59.2 68.4 54.4C71.8 46.6 73.6 36.8 69.8 30.4"
    "C65.5 23.2 55.5 25.5 50 31.5Z"
)


def mark(x, y, d, mono=None):
    """Circle emblem: blue disc, teal 'smile' wave, white tooth outline."""
    s = d / 100.0
    if mono:
        return (
            f'<g transform="translate({x} {y}) scale({s})">'
            f'<circle cx="50" cy="50" r="50" fill="{mono}"/>'
            f'<path d="{TOOTH}" fill="none" stroke="{INK if mono == WHITE else WHITE}" '
            f'stroke-width="5.2" stroke-linejoin="round" stroke-linecap="round"/></g>'
        )
    return (
        f'<g transform="translate({x} {y}) scale({s})">'
        f'<clipPath id="c{int(x)}{int(y)}"><circle cx="50" cy="50" r="50"/></clipPath>'
        f'<g clip-path="url(#c{int(x)}{int(y)})">'
        f'<rect width="100" height="100" fill="{BLUE}"/>'
        f'<path d="M-5 62C18 84 58 92 105 58V105H-5Z" fill="{TEAL}"/>'
        f"</g>"
        f'<path d="{TOOTH}" fill="none" stroke="{WHITE}" stroke-width="5.2" '
        f'stroke-linejoin="round" stroke-linecap="round"/></g>'
    )


def svg(w, h, body, title):
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w:.1f} {h:.1f}" '
        f'width="{w:.0f}" height="{h:.0f}" role="img" aria-label="{title}">'
        f"<title>{title}</title>{body}</svg>\n"
    )


def horizontal(f600, f700, dark_bg=False):
    ink = WHITE if dark_bg else INK
    blue = "#7DC3FF" if dark_bg else BLUE
    teal = "#6FE0D3" if dark_bg else TEAL
    h = 100.0
    m = mark(0, 0, h)
    tx = h + 22
    l1, w1 = f600.run("CABINET DENTAIRE", 17, tx, 38, tracking=0.32)
    a, wa = f700.run("DR. RHOMIJA", 33, tx, 78)
    space = 33 * 0.28
    b, wb = f700.run("NABIL", 33, tx + wa + space, 78)
    w = tx + max(w1, wa + space + wb) + 2
    body = (
        m
        + f'<path d="{l1}" fill="{teal}"/>'
        + f'<path d="{a}" fill="{blue}"/>'
        + f'<path d="{b}" fill="{ink}"/>'
    )
    return svg(w, h, body, "Cabinet Dentaire Dr. Rhomija Nabil")


def stacked(f600, f700):
    d = 120.0
    a, wa = f700.run("DR. RHOMIJA", 30, 0, 0)
    space = 30 * 0.28
    b, wb = f700.run("NABIL", 30, 0, 0)
    l1, w1 = f600.run("CABINET DENTAIRE", 14, 0, 0, tracking=0.32)
    w = max(wa + space + wb, w1, d) + 8
    cx = w / 2
    line2_x = cx - (wa + space + wb) / 2
    a, _ = f700.run("DR. RHOMIJA", 30, line2_x, d + 46)
    b, _ = f700.run("NABIL", 30, line2_x + wa + space, d + 46)
    l1, _ = f600.run("CABINET DENTAIRE", 14, cx - w1 / 2, d + 72, tracking=0.32)
    body = (
        mark(cx - d / 2, 0, d)
        + f'<path d="{a}" fill="{BLUE}"/><path d="{b}" fill="{INK}"/>'
        + f'<path d="{l1}" fill="{TEAL}"/>'
    )
    return svg(w, d + 80, body, "Cabinet Dentaire Dr. Rhomija Nabil")


def main():
    f600, f700, out = Face(sys.argv[1]), Face(sys.argv[2]), sys.argv[3]
    os.makedirs(out, exist_ok=True)
    files = {
        "logo-horizontal.svg": horizontal(f600, f700),
        "logo-horizontal-white.svg": horizontal(f600, f700, dark_bg=True),
        "logo-stacked.svg": stacked(f600, f700),
        "logo-mark.svg": svg(100, 100, mark(0, 0, 100), "Dr. Rhomija Nabil"),
        "logo-mark-white.svg": svg(100, 100, mark(0, 0, 100, mono=WHITE), "Dr. Rhomija Nabil"),
    }
    for name, content in files.items():
        with open(os.path.join(out, name), "w") as fh:
            fh.write(content)

    import cairosvg

    png = os.path.join(out, "png")
    os.makedirs(png, exist_ok=True)
    cairosvg.svg2png(bytestring=files["logo-horizontal.svg"].encode(), write_to=f"{png}/logo-horizontal-1200.png", output_width=1200)
    cairosvg.svg2png(bytestring=files["logo-horizontal-white.svg"].encode(), write_to=f"{png}/logo-horizontal-white-1200.png", output_width=1200)
    cairosvg.svg2png(bytestring=files["logo-stacked.svg"].encode(), write_to=f"{png}/logo-stacked-800.png", output_width=800)
    for size in (512, 192, 180, 32):
        cairosvg.svg2png(bytestring=files["logo-mark.svg"].encode(), write_to=f"{png}/icon-{size}.png", output_width=size, output_height=size)
    print("written:", ", ".join(files))


if __name__ == "__main__":
    main()
