(function ($, Drupal) {
    Drupal.behaviors.carousel_artist_albums = {
        attach: function (context, settings) {
            once('.album-item', '.artist-albums-wrapper', context).forEach(function () {
                $('.artist-albums-wrapper').slick(
                    {
                        centerMode: true,
                        //centerPadding: '240px',
                        slidesToShow: 2,
                        arrows: false,
                        dots: true,
                        responsive: [
                        {
                            breakpoint: 1366,
                            settings: {
                                centerPadding: '160px',
                                slidesToShow: 2,
                            }
                        },
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 1,
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                centerMode: false,
                                slidesToShow: 1,
                            }
                        },
                        {
                            breakpoint: 414,
                            settings: {
                                centerMode: false,
                                slidesToShow: 1,
                            }
                        }
                        ]
                    }
                );
            });
        }
    }
})(jQuery, Drupal);