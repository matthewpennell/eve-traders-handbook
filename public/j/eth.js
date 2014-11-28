$(document).ready(function () {

    $('.losses a').click(function () {
        $('.details').html('<img src="/i/loader.gif" class="loader">');
        $.get(this.href, function (data) {
            $('.details').html(data);
        });
        return false;
    });

});
