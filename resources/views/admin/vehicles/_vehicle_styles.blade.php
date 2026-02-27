<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');

    /* Ruhsat kartı — HTML prototype v5 ile birebir eşleşme */

    /*
     * ŞABLON ÇİZGİLERİ — Hepsi buradan ayarlanır
     * Kart dış çerçevesi, hücre ayırıcıları ve noter alt çizgileri
     * aşağıdaki değişkenlerle kontrol edilir.
     */
    .vehicle-license-root {
        /* Kart dış çerçevesi (mavi kenarlık) */
        --license-border-color: #2554a4;
        --license-border-width: 4px;

        /* Hücreler arası yatay çizgiler (satır ayırıcı) */
        --license-line-color:    rgba(0, 0, 0, 0.55);
        --license-line-width:   1px;

        /* Daha belirgin çizgi (footer vb.) */
        --license-line-strong-color: rgba(0, 0, 0, 0.75);
        --license-line-strong-width: 1.5px;

        /* Orta dikey çizgi (iki kolon arası siyah) */
        --license-grid-vertical-color: #000;
        --license-grid-vertical-width: 1px;

        /* Noter alanları alt çizgisi (Z.3.1, Z.3.2, Z.3.3) */
        --license-noter-line-color: rgba(0, 0, 0, 0.55);
        --license-noter-line-width: 1px;

        /* Eski isimler (uyumluluk) */
        --navy:        #2554a4;
        --blue:        #2554a4;
        --blue-border: var(--license-border-color);
        --line:        var(--license-line-color);
        --line-strong: var(--license-line-strong-color);
        --header-bg:   #2554a4;
        --header-text: #dce8f9;
        --lbl:         #4e6fa0;
        --val:         #18243a;
        --val-soft:    #5a7090;
        --card-bg:     #fafcff;
        --accent-bg:   #eef3fb;
        --red-soft:    #b83232;
        font-family: "Times New Roman", Times, serif;
    }

    .vehicle-license-root .scene {
        width: 100%;
        max-width: 440px;
        height: 600px;
        perspective: 1600px;
        cursor: pointer;
        position: relative;
        filter: none;
    }

    .vehicle-license-root .card3d {
        width: 100%;
        height: 100%;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.88s cubic-bezier(0.645, 0.045, 0.355, 1);
    }

    .vehicle-license-root .card3d.flipped {
        transform: rotateY(180deg);
    }

    .vehicle-license-root .face {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        border-radius: 0;
        overflow: hidden;
    }

    .vehicle-license-root .face-back {
        transform: rotateY(180deg);
    }

    .vehicle-license-root .doc {
        width: 100%;
        height: 100%;
        background: var(--card-bg);
        border: var(--license-border-width) solid var(--license-border-color);
        border-radius: 0;
        display: flex;
        flex-direction: column;
    }

    .vehicle-license-root .doc-header {
        background: var(--header-bg);
        color: var(--header-text);
        text-align: center;
        padding: 5px 8px 4px;
        font-size: 8px;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        line-height: 1.6;
        flex-shrink: 0;
        border-bottom: var(--license-line-strong-width) solid rgba(255, 255, 255, 0.15);
    }

    .vehicle-license-root .doc-header.back-header {
        background: #264070;
    }

    .vehicle-license-root .doc-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Hücreler */
    .vehicle-license-root .cell {
        border-bottom: var(--license-line-width) solid var(--license-line-color);
        padding: 3.5px 8px 3px 14px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }

    .vehicle-license-root .cell-tight {
        padding-left: 6px;
    }

    /* Sağ kolonda orta çizgiden uzaklaştırmak için (F.1, G.2, P.2) */
    .vehicle-license-root .cell-pad-left {
        padding-left: 14px;
    }

    /* Değer sağda (Q) kw/kg */
    .vehicle-license-root .cell-val-right {
        align-items: flex-end;
    }
    .vehicle-license-root .cell-val-right .val {
        margin-left: auto;
    }

    .vehicle-license-root .cell:last-child {
        border-bottom: none;
    }

    .vehicle-license-root .cell.grow {
        flex: 1;
    }

    /* Satırlar */
    .vehicle-license-root .row {
        display: flex;
        border-bottom: var(--license-line-width) solid var(--license-line-color);
        flex-shrink: 0;
    }

    .vehicle-license-root .row > .cell {
        border-bottom: none;
        flex: 1;
    }

    .vehicle-license-root .row > .cell:not(:last-child) {
        border-right: var(--license-grid-vertical-width) solid var(--license-grid-vertical-color);
    }

    .vehicle-license-root .row > .cell.w2  { flex: 2; }
    .vehicle-license-root .row > .cell.w15 { flex: 1.5; }
    /* D.3 geniş, Model yılı (D.4) sol çizgisi sağda */
    .vehicle-license-root .row > .cell.w18 { flex: 2.25; }

    .vehicle-license-root .row.grow {
        flex: 1;
    }

    .vehicle-license-root .row.grow > .cell {
        flex: 1;
    }

    /* Etiket */
    .vehicle-license-root .lbl {
        font-size: 8.5px;
        font-weight: 700;
        color: #000;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        line-height: 1.3;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    /* Değerler */
    .vehicle-license-root .val {
        font-size: 13px;
        font-weight: 600;
        color: #000;
        line-height: 1.35;
        margin-top: 2px;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .vehicle-license-root .val.lg   { font-size: 16px; font-weight: 700; }
    .vehicle-license-root .val.xl   { font-size: 19px; font-weight: 700; letter-spacing: 0.04em; }
    .vehicle-license-root .val.sm   { font-size: 12px; }
    .vehicle-license-root .val.xs   { font-size: 11px; line-height: 1.5; }

    .vehicle-license-root .val.mono {
        font-family: "Times New Roman", Times, serif;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0;
    }

    .vehicle-license-root .val.soft  { color: var(--val-soft); font-weight: 400; }
    .vehicle-license-root .val.red   { color: var(--red-soft); font-weight: 700; }
    .vehicle-license-root .val.navy  { color: var(--navy); font-weight: 700; }

    /* Plaka */
    .vehicle-license-root .plaka-outer {
        display: inline-flex;
        align-items: stretch;
        border: 1.5px solid #1a3060;
        border-radius: 0;
        overflow: hidden;
        margin-top: 4px;
        background: #fff;
        align-self: flex-start;
    }

    .vehicle-license-root .plaka-eu {
        background: #003DA5;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3px 5px;
    }

    .vehicle-license-root .plaka-eu .tr {
        font-size: 8px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.1em;
    }

    .vehicle-license-root .plaka-num {
        font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 18px;
        font-weight: 700;
        color: #080e1c;
        letter-spacing: 0.13em;
        padding: 2px 10px;
        align-self: center;
    }

    /* Footer */
    .vehicle-license-root .doc-footer {
        border-top: var(--license-line-strong-width) solid var(--license-line-strong-color);
        background: var(--accent-bg);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 9px;
        flex-shrink: 0;
        gap: 8px;
    }

    .vehicle-license-root .back-bottom-row > .cell {
        min-height: 44px;
        justify-content: flex-start;
    }

    .vehicle-license-root .ftr-sig {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .vehicle-license-root .badge {
        background: var(--navy);
        color: #fff;
        font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 10.5px;
        font-weight: 600;
        padding: 5px 11px;
        border-radius: 5px;
        letter-spacing: 0.06em;
        text-align: right;
        white-space: nowrap;
    }

    .vehicle-license-root .badge small {
        display: block;
        font-size: 7px;
        font-weight: 400;
        letter-spacing: 0.14em;
        opacity: 0.6;
        margin-bottom: 1px;
    }

    .vehicle-license-root .flip-hint {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 8.5px;
        color: var(--val-soft);
        opacity: 0.75;
    }

    .vehicle-license-root .qr-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    /* Arka yüz: Z.3.1, Z.3.2, Z.3.3 altına çizgi */
    .vehicle-license-root .noter-labels .lbl-line {
        border-bottom: var(--license-noter-line-width) solid var(--license-noter-line-color);
        margin-bottom: 2px;
        min-width: 120px;
        text-align: center;
    }
    .vehicle-license-root .noter-labels .lbl-line .lbl {
        display: block;
        font-size: 7.5px;
        margin-bottom: 1px;
    }

    /* Hover tilt */
    .vehicle-license-root .scene:hover .card3d:not(.flipped) {
        transform: rotateX(2deg) rotateY(-5deg);
    }

    @media (max-width: 992px) {
        .vehicle-license-root .scene {
            max-width: 100%;
            height: 560px;
        }
    }
</style>
