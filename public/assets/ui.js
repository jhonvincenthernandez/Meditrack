// public/assets/ui.js
// Requires jQuery 3.x
(function ($) {
  $(function () {
    var reduceMotion =
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Smooth scroll for in-page anchors
    $('a[href^="#"]').on("click", function (e) {
      var href = this.getAttribute("href");
      if (!href || href === "#") return;
      var $target = $(href);
      if ($target.length) {
        e.preventDefault();
        if (reduceMotion) {
          window.scrollTo(0, $target.offset().top - 64);
          return;
        }
        $("html, body").animate(
          { scrollTop: $target.offset().top - 64 },
          380,
          "swing"
        );
      }
    });

    // Staggered reveal for elements with .reveal
    if (!reduceMotion) {
      $(".reveal").each(function (i) {
        var $el = $(this);
        setTimeout(function () {
          $el.addClass("is-visible");
        }, i * 70);
      });
    } else {
      $(".reveal").addClass("is-visible");
    }

    // Auto-fade flash alerts
    $(".alert").each(function () {
      var $a = $(this);
      if ($a.is(":visible")) {
        $a.delay(120).fadeTo(200, 1).delay(2600).fadeOut(220);
      }
    });

    // Counter up for KPI elements: <span data-count-to="1234">0</span>
    $("[data-count-to]").each(function () {
      var $num = $(this);
      var to = parseFloat($num.data("count-to")) || 0;
      var duration = reduceMotion ? 0 : 900;
      $({ v: 0 }).animate(
        { v: to },
        {
          duration: duration,
          easing: "swing",
          step: function (now) {
            $num.text(Math.floor(now));
          },
          complete: function () {
            $num.text(Math.round(to));
          },
        }
      );
    });

    // Sidebar / topbar enhancements
    // Inject a menu toggle and brand into existing .topbar elements when a .sidebar exists
    function ensureSidebarAccessibility() {
      var $sidebar = $(".sidebar");
      if ($sidebar.length) {
        // ensure sidebar has an id and proper role
        if (!$sidebar.attr('id')) $sidebar.attr('id', 'sidebar');
        $sidebar.attr('role', 'navigation');
        $sidebar.attr('aria-hidden', !$sidebar.hasClass('open'));
      }
    }

    function injectTopbarControls() {
      if ($(".sidebar").length === 0) return; // nothing to toggle

      // create a single menu toggle if none exists in the DOM
      if ($('.menu-toggle').length === 0) {
        var $btn = $(
          '<button type="button" class="menu-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle menu"></button>'
        );
        // build the inner bars
        var $bar = $('<span class="bar" aria-hidden="true"></span>');
        $btn.append($bar);

        // try to prepend into the first .topbar; if none, prepend to body
        var $targetTop = $('.topbar').first();
        if ($targetTop.length) {
          $targetTop.prepend($btn);
        } else {
          $('body').prepend($btn);
        }
      }

      // inject a lightweight brand area if topbar has none (non-destructive)
      // Do not inject any visual brand into the topbar (logo/title removed per request)
    }

    // wire up toggle behavior using delegated handler for .menu-toggle
    $(document).on('click', '.menu-toggle', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var $sidebar = $('#' + ($btn.attr('aria-controls') || 'sidebar'));
      if ($sidebar.length === 0) $sidebar = $('.sidebar').first();
      var willOpen = !$sidebar.hasClass('open');
      $sidebar.toggleClass('open', willOpen).attr('aria-hidden', !willOpen);
      $btn.attr('aria-expanded', willOpen ? 'true' : 'false');
    });

    // close the sidebar when clicking outside on small screens
    $(document).on('click', function (e) {
      var $sidebar = $('.sidebar.open');
      if ($sidebar.length === 0) return;
      var $target = $(e.target);
      if ($target.closest('.sidebar').length === 0 && $target.closest('.menu-toggle').length === 0) {
        $sidebar.removeClass('open').attr('aria-hidden', 'true');
        $('.menu-toggle').attr('aria-expanded', 'false');
      }
    });

    // ESC to close
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        var $sidebar = $('.sidebar.open');
        if ($sidebar.length) {
          $sidebar.removeClass('open').attr('aria-hidden', 'true');
          $('.menu-toggle').attr('aria-expanded', 'false');
        }
      }
    });

    // Initialize enhancements
    ensureSidebarAccessibility();
    injectTopbarControls();
  });
})(jQuery);
