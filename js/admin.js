jQuery(document).ready(function($) {

     $(".gktppDropdown").mouseover(function() {
          $(this).siblings("dd").find("ul").slideToggle(100);
     });

     $('.dropdown dd div ul').mouseover(function() {
          $(this).css( 'display', 'block');
     });


     // take care of page/post checkboxes
     var checkAllPagesOrPosts = $('.gktppCheckAll');
     var visibleCBs = $('.gktppVisibleCBs');
     var hiddenCB = $('.gktppHiddenCB');
     var checkAll = $('.gktppCheckAll');


     checkAllPagesOrPosts.click(function() {

          if ($(this).is(':checked')) {
               $(this).parent().siblings().children().prop('checked', true);
          }

          else if (! $(this).is(':checked')) {
               $(this).parent().siblings().children().prop('checked', false);
          }
     });

     // check the hidden checkbox (to show page/post titles) when a visible box is checked.
     visibleCBs.click(function() {
          if ($(this).is(':checked')) {
               $(this).next(hiddenCB).prop('checked', true);
          }

          else if (!$(this).is(':checked')) {
               $(this).next(hiddenCB).prop('checked', false);
          }
     });


     // when the option to insert into all pages/posts is clicked, this checks all page and post values for ID's and titles
     var CBcheckAllPagesPosts = $('#allPagesPostsCheck');

     function selectAllCBs( $arr, $bool ) {
          $.each($arr, function(index, value) {
               $(this).prop('checked', $bool );
          });
     }

     CBcheckAllPagesPosts.click(function() {
          var allCheckBoxes = [visibleCBs, hiddenCB, checkAll];

          if ( $(this).is(':checked')) {
               selectAllCBs( allCheckBoxes, true );
          }

          if ( !$(this).is(':checked')) {
               selectAllCBs( allCheckBoxes, false );
          }

     });

});


// remind user to enter url input field
jQuery(document).ready(function($) {

     var urlInput = $('#gktpp-url-input');
     urlInput.blur(function() {

          if ( !urlInput.val() ) {
               urlInput.addClass("warning");
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
