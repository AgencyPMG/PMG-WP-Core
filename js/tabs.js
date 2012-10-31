jQuery(document).ready(function($) {
    $('.pmgcore-tab-nav a').on('click', function(e) {
        e.preventDefault();
        $(this).parents('.pmgcore-tab-nav').find('li').removeClass('active');
        $(this).parent('li').addClass('active');

        var g = $(this).attr('data-group')
          , id = $(this).attr('data-id');
        
        $('.pmgcore-tab.' + g).hide();
        $('.pmgcore-tab.'+ g + '#' + id).show();
    });

    $('.pmgcore-tab-nav').each(function() {
        $(this).find('li a').first().trigger('click');
    });
});
