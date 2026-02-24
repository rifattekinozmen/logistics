<style>
/* Türkiye Cumhuriyeti Kimlik Kartı – Ön/Arka yüz, flip */
.tr-id-stage {
    perspective: 1000px;
    position: relative;
}
.tr-id-card-wrapper {
    position: relative;
    width: 100%;
    max-width: 428px;
    aspect-ratio: 856 / 540;
    margin: 0 auto;
    transform-style: preserve-3d;
    transition: transform 0.6s ease;
}
.tr-id-stage.tr-id-flipped .tr-id-card-wrapper {
    transform: rotateY(180deg);
}
.tr-id-card {
    position: absolute;
    inset: 0;
    background: #f8f9fb;
    border-radius: 32px;
    border: 1px solid #dfe3ea;
    overflow: hidden;
    backface-visibility: hidden;
}
.tr-id-card-dots {
    position: absolute;
    inset: 0;
    background: radial-gradient(#d5dae3 1px, transparent 1px);
    background-size: 12px 12px;
    opacity: 0.25;
    pointer-events: none;
}
.tr-id-back {
    transform: rotateY(180deg);
}

/* ---------- ÖN YÜZ — ISO 856×540 grid ---------- */
.tr-id-front {
    display: grid;
    grid-template-rows: auto 1fr;
    padding: 0;
}
.tr-id-header {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 8px 12px 8px;
    color: #1A1A1A;
}
.tr-id-title {
    margin: 0 0 2px 0;
    font-family: "Source Sans Pro", ui-sans-serif, sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.tr-id-subtitle {
    display: block;
    font-size: 7px;
    color: #4a4a4a;
    letter-spacing: 0.02em;
}
.tr-id-flag {
    position: absolute;
    right: 7%;
    top: 13%;
    width: 30%;
    aspect-ratio: 3 / 2;
    opacity: 0.95;
}
.tr-id-idno {
    position: absolute;
    left: 7%;
    top: 20%;
    z-index: 2;
    width: 22%;
    text-align: center;
    font-family: "Source Sans Pro", Bahnschrift, "DIN 2014", Arial, sans-serif;
    font-size: 6px;
    font-weight: 400;
    color: #4a4a4a;
    letter-spacing: 0.02em;
}
.tr-id-idno-num {
    position: absolute;
    left: 7%;
    top: 23%;
    z-index: 2;
    width: 22%;
    text-align: center;
    font-family: ui-monospace, monospace;
    font-size: 10px;
    font-weight: 700;
    color: #1A1A1A;
    letter-spacing: 0.02em;
}
.tr-id-photo-main {
    position: absolute;
    left: 7%;
    top: 75px;
    width: 22%;
    height: 125px;
    border-radius: 12px;
    overflow: hidden;
    background: #e9edf3;
    border: 1px solid #cfd4dc;
}
.tr-id-photo-placeholder,
.tr-id-photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tr-id-photo-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}
.tr-id-photo-placeholder .material-symbols-outlined {
    font-size: 1.25rem;
    color: #94a3b8;
}
.tr-id-hologram {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.25) 0%, rgba(200,200,220,0.15) 50%, transparent 100%);
    opacity: 0.18;
    mix-blend-mode: overlay;
    transform: rotate(25deg);
    transform-origin: 50% 50%;
    pointer-events: none;
}
.tr-id-info {
    position: absolute;
    left: 30.2%;
    top: 26%;
    right: 6.6%;
    bottom: 18%;
    line-height: 1.2;
}
.tr-id-cell-a1,
.tr-id-cell-a2,
.tr-id-cell-a3,
.tr-id-cell-a4,
.tr-id-cell-a5,
.tr-id-cell-b3,
.tr-id-cell-b4,
.tr-id-cell-b5 {
    position: absolute;
}
.tr-id-cell-a1 { left: 0; top: 0; width: 48%; }
.tr-id-cell-a2 { left: 0; top: 19%; width: 48%; }
.tr-id-cell-a3 { left: 0; top: 38%; width: 48%; }
.tr-id-cell-a4 { left: 0; top: 57%; width: 48%; }
.tr-id-cell-a5 { left: 0; top: 76%; width: 48%; }

.tr-id-cell-b1,
.tr-id-cell-b2 {
    display: none;
}
.tr-id-cell-b3 { left: 52%; top: 38%; width: 48%; }
.tr-id-cell-b4 { left: 52%; top: 57%; width: 48%; }
.tr-id-cell-b5 { left: 52%; top: 76%; width: 48%; }
.tr-id-row-cinsiyet {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 6px;
    width: 100%;
}
.tr-id-signature {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    width: 100%;
}
.tr-id-signature .tr-id-label {
    flex-shrink: 0;
    font-size: 6px;
    font-weight: 400;
    color: #4a4a4a;
}
.tr-id-signature-line {
    flex: 1;
    min-width: 40px;
    height: 0;
    border-bottom: 1px solid #aaa;
    margin-bottom: 3px;
}
.tr-id-photo-mini-wrap {
    flex-shrink: 0;
    width: 24px;
    height: 30px;
    border-radius: 50%;
    overflow: hidden;
    background: #e9edf3;
    border: 1px solid #cfd4dc;
}
.tr-id-photo-mini-placeholder,
.tr-id-photo-mini-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tr-id-photo-mini-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}
.tr-id-photo-mini-placeholder .material-symbols-outlined {
    font-size: 0.7rem;
    color: #94a3b8;
}
.tr-id-field {
    margin-bottom: 1px;
}
.tr-id-label {
    font-family: "Source Sans Pro", Bahnschrift, Arial, sans-serif;
    font-size: 6px;
    font-weight: 400;
    color: #4a4a4a;
    letter-spacing: 0.02em;
}
.tr-id-value {
    font-family: "Source Sans Pro", Bahnschrift, Arial, sans-serif;
    font-size: 8px;
    font-weight: 600;
    color: #1A1A1A;
    letter-spacing: 0.02em;
}
.tr-id-info .tr-id-field {
    margin-bottom: 0;
}

/* ---------- ARKA YÜZ ---------- */
.tr-id-back-inner {
    padding: 8% 10%;
    height: 100%;
    position: relative;
}
.tr-id-back-header {
    font-family: "Source Sans Pro", ui-sans-serif, sans-serif;
    font-size: 9px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #1A1A1A;
    letter-spacing: 0.02em;
}
.tr-id-barcode {
    width: 100%;
    height: 20px;
    background: repeating-linear-gradient(
        to right,
        #000,
        #000 2px,
        transparent 2px,
        transparent 4px
    );
    margin-bottom: 12px;
}
.tr-id-back-grid {
    display: flex;
    gap: 12px;
    margin-bottom: 8px;
}
.tr-id-chip {
    width: 22%;
    aspect-ratio: 120 / 90;
    border-radius: 8px;
    background: #d9d9d9;
    box-shadow: inset 0 0 0 2px #aaa;
    flex-shrink: 0;
}
.tr-id-back-fields {
    flex: 1;
    min-width: 0;
}
.tr-id-back-fields .tr-id-label {
    font-size: 6px;
    color: #4a4a4a;
}
.tr-id-back-fields .tr-id-value {
    font-size: 8px;
    color: #1A1A1A;
}
.tr-id-issuing {
    font-weight: 600;
}
.tr-id-mrz {
    position: absolute;
    left: 8%;
    right: 8%;
    bottom: 6%;
    background: #eef1f6;
    border-radius: 6px;
    padding: 6px 8px;
    font-family: ui-monospace, monospace;
    font-size: clamp(8px, 3.5vw, 12px);
    letter-spacing: 0.06em;
    white-space: pre;
    overflow: hidden;
    line-height: 1.35;
    box-sizing: border-box;
}

/* Flip butonu */
.tr-id-flip-btn {
    display: block;
    margin: 12px auto 0;
    padding: 8px 16px;
    background: #2e3c72;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
}
.tr-id-flip-btn:hover {
    background: #3d4d88;
}

/* Eski uyumluluk */
.personel-id-card,
.personel-id-card-expanded {
    min-width: 100%;
}
.personnel-contact-btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    line-height: 1;
}
.personnel-contact-btn .material-symbols-outlined {
    font-size: 1rem;
    flex-shrink: 0;
}
.cursor-pointer {
    cursor: pointer;
}
.personnel-header-card,
.personnel-avatar-wrap {
    overflow: visible !important;
}
.personnel-contact-btn {
    white-space: nowrap;
}
</style>
