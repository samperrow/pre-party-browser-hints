jQuery(document).ready(function($) {
     var clickTarget = $('.gktpp-collapse-btn');
     clickTarget.on('click', function() {
          $(this).next('div').toggleClass('hide');
          $(this).find( $('button > span')).toggleClass('active');
     });
});
