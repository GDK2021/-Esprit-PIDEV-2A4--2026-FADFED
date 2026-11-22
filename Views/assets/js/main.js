(function ($) {
    "use strict";

    // Spinner : disparaît une fois la page chargée
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 300);
    };
    spinner();

    // Bouton "retour en haut"
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });

    // Menu déroulant ouvert au survol (écrans larges)
    $(window).on('load resize', function () {
        if (this.matchMedia('(min-width: 992px)').matches) {
            $('.navbar .dropdown').hover(
                function () { $(this).addClass('show').find('.dropdown-menu').addClass('show'); },
                function () { $(this).removeClass('show').find('.dropdown-menu').removeClass('show'); }
            );
        } else {
            $('.navbar .dropdown').off('mouseenter mouseleave');
        }
    });
})(jQuery);
