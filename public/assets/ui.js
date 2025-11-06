// public/assets/ui.js
// Requires jQuery 3.x
(function ($) {
  $(function () {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Smooth scroll for in-page anchors
    $('a[href^="#"]').on('click', function (e) {
      var href = this.getAttribute('href');
      if (!href || href === '#') return;
      var $target = $(href);
      if ($target.length) {
        e.preventDefault();
        if (reduceMotion) { window.scrollTo(0, $target.offset().top - 64); return; }
        $('html, body').animate({ scrollTop: $target.offset().top - 64 }, 380, 'swing');
      }
    });

    // Staggered reveal for elements with .reveal
    if (!reduceMotion) {
      $('.reveal').each(function (i) {
        var $el = $(this);
        setTimeout(function () { $el.addClass('is-visible'); }, i * 70);
      });
    } else {
      $('.reveal').addClass('is-visible');
    }

    // Auto-fade flash alerts
    $('.alert').each(function () {
      var $a = $(this);
      if ($a.is(':visible')) {
        $a.delay(120).fadeTo(200, 1).delay(2600).fadeOut(220);
      }
    });

    // Counter up for KPI elements: <span data-count-to="1234">0</span>
    $('[data-count-to]').each(function () {
      var $num = $(this);
      var to = parseFloat($num.data('count-to')) || 0;
      var duration = reduceMotion ? 0 : 900;
      $({ v: 0 }).animate({ v: to }, {
        duration: duration,
        easing: 'swing',
        step: function (now) { $num.text(Math.floor(now)); },
        complete: function () { $num.text(Math.round(to)); }
      });
    });

    // Sidebar toggle (requires a button with id="menuToggle")
    $('#menuToggle').on('click', function () {
      $('.sidebar').toggleClass('open');
    });
  });
})(jQuery);
