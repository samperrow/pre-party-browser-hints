jQuery(document).ready(function($) {
     var clickTarget = document.getElementById("gktpp-collapse-btn");
     var box = document.getElementById("gktpp-collapse-box");
     var arrow = document.getElementById("gktpp-toggle-indicator")

     clickTarget.onclick = function() {
          box.classList.toggle("hide");
          arrow.classList.toggle("active");
     }
});
