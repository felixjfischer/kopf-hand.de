var $j = jQuery.noConflict();
$j(document).ready(function(){
    // Megamenu-Handling bleibt erhalten
    $j('.dropdown-menu').each(function(i){
        var idx = i + 1;
        var $dropdown = $j('.dropdown-menu-' + idx);
        var $mainMenuItem = $j('.first-level-' + idx + ' > a');
        $dropdown.insertAfter($mainMenuItem);
    });

    var $firstLevel = $j('.et_mobile_menu .first-level > a');
    var $allDropdowns = $j('.et_mobile_menu .dropdown-menu');

    $firstLevel.off('click').on('click', function(e) {
        e.preventDefault();
        var $this = $j(this);
        var $thisDropdown = $this.siblings('.dropdown-menu');
        $thisDropdown.slideToggle(300);
        $this.toggleClass('icon-switch');
        $allDropdowns.not($thisDropdown).slideUp(300);
        $firstLevel.not($this).removeClass('icon-switch');
    });

    var $nav = $j('#main-nav');
    var $mobileMenu = $j('.et_pb_menu .et_mobile_menu');
    var lastScrollTop = $j(window).scrollTop();
    var scrollThresholdHide = $j(window).height() * 0.1;
    var scrollThresholdShow = 20;
    var lastHiddenPosition = 0;
    
    $j(window).on('scroll', function() {
        var scrollTop = $j(this).scrollTop();
        if(scrollTop > lastScrollTop && scrollTop > scrollThresholdHide) {
            $nav.addClass('nav-hidden');
            $mobileMenu.addClass('nav-hidden');
            lastHiddenPosition = scrollTop;
        } else if(scrollTop < lastHiddenPosition - scrollThresholdShow) {
            $nav.removeClass('nav-hidden');
            $mobileMenu.removeClass('nav-hidden');
            $allDropdowns.filter(':visible').stop().slideUp(200);
            $firstLevel.removeClass('icon-switch');
        }
        lastScrollTop = scrollTop;
    });
});
