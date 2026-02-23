<style>
/* Türkiye Cumhuriyeti Kimlik Kartı – Referans tasarım */
.tr-id-card {
    position: relative;
    background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 50%, #dcf4fa 100%);
    border-radius: 24px;
    padding: 0;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
    overflow: hidden;
    aspect-ratio: 1.586 / 1;
    max-width: 100%;
}
.tr-id-card-dots {
    position: absolute;
    inset: 0;
    opacity: 0.1;
    pointer-events: none;
    background-image: radial-gradient(#0ea5e9 0.5px, transparent 0.5px);
    background-size: 10px 10px;
}
.tr-id-card-inner {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 1rem 1.25rem;
}
.tr-id-card-header {
    text-align: center;
    margin-bottom: 0.75rem;
}
.tr-id-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0;
}
.tr-id-card-subtitle {
    font-size: 0.625rem;
    font-weight: 500;
    color: #64748b;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin: 0.25rem 0 0;
}
.tr-id-card-grid {
    display: grid;
    grid-template-columns: 1fr 1.8fr;
    gap: 1rem;
    flex: 1;
    min-height: 0;
}
.tr-id-col-photo {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: center;
}
.tr-id-photo-wrap {
    width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.3);
    flex-shrink: 0;
}
.tr-id-photo,
.tr-id-photo-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tr-id-photo-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
}
.tr-id-photo-placeholder .material-symbols-outlined {
    font-size: 2.5rem;
    color: #94a3b8;
}
.tr-id-col-fields {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-width: 0;
}
.tr-id-flag-pos {
    position: relative;
}
.tr-id-flag-svg {
    position: absolute;
    top: -0.25rem;
    right: 0;
    width: 48px;
    height: 34px;
    opacity: 0.9;
}
.tr-id-field-block {
    flex-shrink: 0;
}
.tr-id-label {
    font-size: 0.5rem;
    line-height: 1.2;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.tr-id-value {
    font-size: 0.75rem;
    font-weight: 700;
    color: #1e293b;
    min-height: 1rem;
}
.tr-id-fields-row {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}
.tr-id-fields-2col {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.5rem;
}
.tr-id-col-uyruk {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}
.tr-id-photo-mini {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.4);
    border: 1px solid rgba(148, 163, 184, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}
.tr-id-photo-mini-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tr-id-photo-mini-placeholder {
    font-size: 1.25rem !important;
    color: #94a3b8 !important;
}
.tr-id-signature {
    font-size: 0.7rem;
    color: #64748b;
    min-height: 1.25rem;
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
