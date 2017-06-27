// remind user to enter url input field
jQuery(document).ready(function($) {

     var urlInput = $('#gktpp-url-input');
     urlInput.blur(function() {

          if ( !urlInput.val() ) {
               urlInput.addClass("warning");
               // urlInput.toggleClass("warning");
          }

          if ( urlInput.val() ) {
               urlInput.removeClass("warning");
          }

     });
});

// open help tip on hover
jQuery(document).ready(function($) {
     var helpBox = $('.gktpp-help-tip-hint');

     helpBox.mouseover(function() {
          $(this).siblings("p").css({ "display": "block" });
     });

     helpBox.mouseout(function() {
          $(this).siblings("p").css({ "display": "none" });
     });

});
