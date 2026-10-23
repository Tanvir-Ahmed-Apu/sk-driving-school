<div class="topbar <?= !empty($topbarMobile) ? 'topbar-mobile' : 'topbar-desktop' ?> py-2">
    <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">

        
        <div class="topbar-social d-flex align-items-center justify-content-center" aria-label="Social media">
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>

        
        <div class="topbar-info d-flex flex-column flex-lg-row align-items-center gap-2 gap-lg-4 small">

            <a href="tel:+16464000666">
                <i class="bi bi-telephone-fill me-2"></i>(646)-400-0666
            </a>

            <a href="https://maps.google.com/?q=60-15+Roosevelt+Ave,+2nd+floor,+Woodside,+Queens,+New+York+11377"
               target="_blank" rel="noopener noreferrer">
                <i class="bi bi-geo-alt-fill me-2"></i>60-15 Roosevelt Ave, Woodside, NY 11377
            </a>

            <div class="topbar-hours d-flex flex-column align-items-center align-items-lg-start">
                <span><i class="bi bi-clock-fill me-2 d-lg-none"></i>Mon - Sat: 7am to 10pm</span>
                <span>Sun: 10am to 5pm</span>
            </div>

        </div>
    </div>
</div>