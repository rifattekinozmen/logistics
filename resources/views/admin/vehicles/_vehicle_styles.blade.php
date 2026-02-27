<style>
.vehicle-license-stage {
    perspective: 1000px;
    position: relative;
}
.vehicle-license-pages {
    position: relative;
    width: 100%;
    max-width: 420px;
    margin-inline: auto;
    height: 550px;
    transform-style: preserve-3d;
    transition: transform 0.6s ease;
}
.vehicle-license-stage.vehicle-license-back .vehicle-license-pages {
    transform: rotateY(180deg);
}
.vehicle-license-front-page,
.vehicle-license-back-page {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    display: flex;
    align-items: stretch;
    justify-content: stretch;
}
.vehicle-license-back-page {
    transform: rotateY(180deg);
}
</style>

