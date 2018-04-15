jQuery(document).ready(function($) {
     var clickTarget = $('.gktpp-collapse-btn');
     clickTarget.on('click', function() {
          $(this).next('div').toggleClass('hide');
          $(this).find( $('button > span')).toggleClass('active');
     });
});

function emailValidate(e) {
     var email = document.getElementById("gktpp-email");
     var errorMSg = document.getElementById("gktpp-error-message");
     var mailformat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

     if (email.value.match(mailformat)) {
          errorMSg.style.display = "none";
          email.style.backgroundColor = "#cfebde";
          email.focus();
          return true;
     } else {
          e.preventDefault();
          errorMSg.style.display = "inline-block";
          email.style.backgroundColor = "#fce4e2";
          email.focus();
          return false;
     }
}
