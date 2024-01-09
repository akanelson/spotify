(function ($, Drupal) {
    Drupal.behaviors.carousel_top_songs = {
        attach: function (context, settings) {
            once('.song-item', '.top-20-songs-wrapper', context).forEach(function () {
                $('.top-20-songs-wrapper').slick(
                    {
                        centerMode: true,
                        centerPadding: '240px',
                        slidesToShow: 2,
                        arrows: false,
                        dots: true,
                        autoplay:true,
                        autoplaySpeed: 2000,
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