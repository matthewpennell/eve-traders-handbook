$(document).ready(function () {

    // Clicking an item in the table loads the details via Ajax.
    $('.losses a').click(function () {
        $('.details').html('<img src="/i/loader.gif" class="loader">');
        $.get(this.href, function (data) {
            $('.details').html(data);
        });
        return false;
    });

    // Clicking on filter checkboxes submits the form to reload the page.
    $('.filters input').click(function () {
        $('.filters')[0].submit();
    });

});
