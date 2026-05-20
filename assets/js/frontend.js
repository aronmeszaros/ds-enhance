(function ($) {
    'use strict';

    function toInt(value, fallback) {
        var parsed = parseInt(value, 10);
        return Number.isNaN(parsed) ? fallback : parsed;
    }

    function toBool(value, fallback) {
        if (typeof value === 'boolean') {
            return value;
        }

        if (typeof value === 'string') {
            var normalized = value.toLowerCase();
            if (normalized === 'true' || normalized === '1') {
                return true;
            }
            if (normalized === 'false' || normalized === '0') {
                return false;
            }
        }

        return fallback;
    }

    function getResponsiveSettings(baseSlidesToShow) {
        return [
            {
                breakpoint: 1280,
                settings: {
                    slidesToShow: Math.min(baseSlidesToShow, 3)
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: Math.min(baseSlidesToShow, 2)
                }
            },
            {
                breakpoint: 640,
                settings: {
                    slidesToShow: 1
                }
            }
        ];
    }

    function getSlickInstance($carousel) {
        if (!$carousel || $carousel.length === 0) {
            return null;
        }

        var element = $carousel.get(0);
        if (element && element.slick) {
            return element.slick;
        }

        if (typeof $.fn.slick !== 'function') {
            return null;
        }

        try {
            return $carousel.slick('getSlick');
        } catch (error) {
            return null;
        }
    }

    function bindArrowState($carousel, $prevButton, $nextButton) {
        var updateState = function () {
            var slick = getSlickInstance($carousel);
            if (!slick) {
                return;
            }

            var currentSlide = slick.currentSlide;
            var slidesToShow = slick.options.slidesToShow;
            var maxStartIndex = Math.max(slick.slideCount - slidesToShow, 0);

            $prevButton.prop('disabled', currentSlide <= 0);
            $nextButton.prop('disabled', currentSlide >= maxStartIndex);
        };

        $carousel.on('init.dseSlick reInit.dseSlick afterChange.dseSlick breakpoint.dseSlick', updateState);
        updateState();
    }

    function bindCarouselControls($carousel) {
        var carouselId = $carousel.attr('id');
        var $arrows = carouselId
            ? $('.digi-posts-arrows[data-carousel-id="' + carouselId + '"]')
            : $();

        var $prevButton = $arrows.find('.slick-prev-custom');
        var $nextButton = $arrows.find('.slick-next-custom');

        console.log('[DSE] bindCarouselControls', {
            carouselId: carouselId,
            initialized: $carousel.hasClass('slick-initialized'),
            arrowGroups: $arrows.length,
            prevButtons: $prevButton.length,
            nextButtons: $nextButton.length
        });

        $prevButton.off('click.dseSlick').on('click.dseSlick', function (event) {
            event.preventDefault();

            if (!getSlickInstance($carousel)) {
                initSingleCarousel($carousel);
            }

            if (getSlickInstance($carousel)) {
                $carousel.slick('slickPrev');
            }
        });

        $nextButton.off('click.dseSlick').on('click.dseSlick', function (event) {
            event.preventDefault();

            if (!getSlickInstance($carousel)) {
                initSingleCarousel($carousel);
            }

            if (getSlickInstance($carousel)) {
                $carousel.slick('slickNext');
            }
        });

        bindArrowState($carousel, $prevButton, $nextButton);
    }

    function initSingleCarousel($carousel) {
        bindCarouselControls($carousel);

        if (getSlickInstance($carousel) || typeof $.fn.slick !== 'function') {
            return;
        }

        var slidesToShow = toInt($carousel.data('slides-to-show'), 4);
        var slidesToScroll = toInt($carousel.data('slides-to-scroll'), 1);
        var autoplay = toBool($carousel.data('autoplay'), false);
        var autoplaySpeed = toInt($carousel.data('autoplay-speed'), 5000);
        var infinite = toBool($carousel.data('infinite'), false);

        $carousel.slick({
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            autoplay: autoplay,
            autoplaySpeed: autoplaySpeed,
            infinite: infinite,
            arrows: false,
            dots: false,
            adaptiveHeight: false,
            responsive: getResponsiveSettings(slidesToShow)
        });
    }

    function initCarousels() {
        $('.slick-carousel').each(function () {
            initSingleCarousel($(this));
        });
    }

    function bindGaTracking() {
        $(document)
            .off('click.dseGaTracking', '[data-ga-event]')
            .on('click.dseGaTracking', '[data-ga-event]', function (event) {
                var $target = $(event.currentTarget);

                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    event: $target.attr('data-ga-event'),
                    type: $target.attr('data-title') || ''
                });
            });
    }

    $(function () {
        initCarousels();
        bindGaTracking();

        // If another script initializes slick later, re-bind custom controls once more.
        $(window).on('load', initCarousels);
    });
})(jQuery);
