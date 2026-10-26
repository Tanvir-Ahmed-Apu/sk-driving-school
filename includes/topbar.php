<div class="topbar <?= !empty($topbarMobile) ? 'topbar-mobile' : 'topbar-desktop' ?> py-2">
    <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">



        <div class="topbar-social">
            <a href="#"><i class="fa-brands fa-square-facebook" style="color: rgb(8, 102, 255);"></i></a>
            <a href="#"><i class="fa-brands fa-instagram instagram-color"></i></a>
            <a href="#"><i class="fa-brands fa-x-twitter" style="color: rgb(0, 0, 0);"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin" style="color: rgb(0, 102, 194);"></i></a>
            <a href="#"><i class="fa-brands fa-youtube" style="color: rgb(255, 0, 0);"></i></a>
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
                <span><i class="bi bi-clock-fill me-2 d-lg-none"></i>Mon - Sat: 7am to 10pm to Sun: 10am to 5pm</span>
                <span></span>
            </div>

        </div>
    </div>
</div>