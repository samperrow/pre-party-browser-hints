
// pprhUpgrade()
function pprhUpgrade() {

    var checkoutBtn = document.getElementById('pprh-checkout');

    console.log(pprh_checkout_data);

    function checkoutEvtListener() {
        checkoutBtn.addEventListener('click', calcAndOpenCheckoutModal);
    }

    function calcAndOpenCheckoutModal() {
        var top = ((screen.height - 800) / 2) - 40;
        var left = (screen.width - 600) / 2;
        var url = 'https://sphacks.io/checkout';
        window.open(url, '_blank', 'height=800, width=800, top=' + top + ',left=' + left );
    }

}