$(document).ready(function () {

    // Clicking an item in the table loads the details via Ajax.
    $('.losses tbody a').click(function () {
        $('.details').html('<img src="/i/loader.gif" class="loader">');
        $.get(this.href, function (data) {
            $('.details').html(data);
        });
        return false;
    });

    // Clicking on filter checkboxes submits the form to reload the page.
    $('.filters input[type=checkbox]').click(function () {
        $('.filters')[0].submit();
    });

    // Updating the Material Efficiency dropdown changes the amounts/costs and saves the value.
    $('body').delegate('#material-efficiency', 'change', function () {
        var typeID = $('.details h3').attr('id');
        $('.details').html('<img src="/i/loader.gif" class="loader">');
        $.get('details/' + typeID, {
            me: this.value
        }, function (data) {
            $('.details').html(data);
        });
    });

});
