#!/usr/bin/env python3
"""Generate Gulf Coast / scan-to-try QR assets for ilovepbj.

Primary target: https://ilovepbj.shop/demo  (one-tap Peek — no register wall)
Secondary (optional): https://ilovepbj.shop/food-cost-calculator

Replaces older playground-register QR stickers.
"""
from __future__ import annotations

import argparse
from pathlib import Path

import qrcode
from PIL import Image, ImageDraw, ImageFont
from qrcode.constants import ERROR_CORRECT_H

ROOT = Path(__file__).resolve().parents[1]
DEMO_URL = "https://ilovepbj.shop/demo"
FOOD_COST_URL = "https://ilovepbj.shop/food-cost-calculator"
QR_FILL = (58, 47, 31)
PURPLE = (107, 74, 140)
CREAM = (252, 248, 238)


def make_qr(url: str, box_size: int = 14, border: int = 2) -> Image.Image:
    qr = qrcode.QRCode(version=None, error_correction=ERROR_CORRECT_H, box_size=box_size, border=border)
    qr.add_data(url)
    qr.make(fit=True)
    return qr.make_image(fill_color=QR_FILL, back_color="white").convert("RGBA")


def overlay_logo(qr_img: Image.Image, logo_path: Path, frac: float = 0.22) -> Image.Image:
    if not logo_path.is_file():
        return qr_img
    logo = Image.open(logo_path).convert("RGBA")
    qw, qh = qr_img.size
    target = max(48, int(min(qw, qh) * frac))
    logo.thumbnail((target, target), Image.Resampling.LANCZOS)
    pad = 10
    lw, lh = logo.size
    plate = Image.new("RGBA", (lw + pad * 2, lh + pad * 2), (255, 255, 255, 0))
    draw = ImageDraw.Draw(plate)
    draw.rounded_rectangle((0, 0, plate.size[0] - 1, plate.size[1] - 1), radius=14, fill=(255, 255, 255, 255))
    plate.paste(logo, (pad, pad), logo)
    out = qr_img.copy()
    out.paste(plate, ((qw - plate.size[0]) // 2, (qh - plate.size[1]) // 2), plate)
    return out


def caption_sticker(qr_img: Image.Image, caption: str = "scan to try ilovepbj") -> Image.Image:
    qw, qh = qr_img.size
    margin = 28
    text_h = 64
    canvas = Image.new("RGBA", (qw + margin * 2, qh + margin + text_h), (*CREAM, 255))
    canvas.paste(qr_img, (margin, margin), qr_img)
    draw = ImageDraw.Draw(canvas)
    font = None
    for candidate in (
        ROOT / "Fonts" / "dreaming-outloud-pro-regular.otf",
        ROOT / "Fonts" / "MouseMemoirs-Regular.ttf",
        ROOT / "Fonts" / "modern-love-caps.ttf",
    ):
        if candidate.is_file():
            try:
                font = ImageFont.truetype(str(candidate), 36)
                break
            except OSError:
                continue
    if font is None:
        font = ImageFont.load_default()
    bbox = draw.textbbox((0, 0), caption, font=font)
    tw = bbox[2] - bbox[0]
    draw.text(((canvas.size[0] - tw) // 2, margin + qh + 8), caption, fill=PURPLE + (255,), font=font)
    return canvas


def write_png(img: Image.Image, path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    rgb = Image.new("RGB", img.size, CREAM)
    rgb.paste(img, mask=img.split()[-1] if img.mode == "RGBA" else None)
    rgb.save(path, "PNG", optimize=True)
    print(f"wrote {path} ({path.stat().st_size} bytes)")


def main() -> None:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--url", default=DEMO_URL)
    ap.add_argument("--also-food-cost", action="store_true")
    args = ap.parse_args()

    logo = ROOT / "icon-192.png"
    if not logo.is_file():
        logo = ROOT / "my-logo.png"

    plain = make_qr(args.url, box_size=12, border=2)
    write_png(plain, ROOT / "assets" / "growth" / "qr-demo.png")
    write_png(plain, ROOT / "uploads" / "docs" / "qr-demo.png")

    branded = overlay_logo(make_qr(args.url, box_size=14, border=2), logo)
    sticker = caption_sticker(branded)
    write_png(sticker, ROOT / "assets" / "growth" / "scan-to-try-qr.png")
    write_png(sticker, ROOT / "uploads" / "docs" / "scan-to-try-qr.png")
    write_png(sticker, ROOT / "assets" / "growth" / "playground-signup-qr.png")

    drop = Path("/workspace")
    write_png(sticker, drop / "ilovepbj-scan-to-try-qr.png")
    write_png(plain, drop / "ilovepbj-playground-signup-qr.png")

    meta = ROOT / "assets" / "growth" / "QR-TARGETS.txt"
    meta.write_text(
        "ilovepbj growth QR targets\n"
        "=========================\n"
        f"Primary (Peek, no signup): {args.url}\n"
        f"Secondary (food cost):     {FOOD_COST_URL}\n"
        "\n"
        "Files:\n"
        "  assets/growth/qr-demo.png              — plain QR → primary\n"
        "  assets/growth/scan-to-try-qr.png       — branded sticker → primary\n"
        "  assets/growth/playground-signup-qr.png — same as sticker (legacy name)\n"
        "  uploads/docs/qr-demo.png / scan-to-try-qr.png — print copies\n"
        "\n"
        "Regenerate:\n"
        "  /workspace/qr-venv/bin/python cli/generate-growth-qr.py\n",
        encoding="utf-8",
    )
    print(f"wrote {meta}")

    if args.also_food_cost:
        fc = make_qr(FOOD_COST_URL, box_size=12, border=2)
        write_png(fc, ROOT / "assets" / "growth" / "qr-food-cost.png")
        write_png(fc, ROOT / "uploads" / "docs" / "qr-food-cost.png")


if __name__ == "__main__":
    main()
