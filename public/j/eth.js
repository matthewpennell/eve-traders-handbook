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

    // Removing a solar system ID from the list.
    $('body').delegate('.remove-system', 'click', function () {
        var solarSystemID = $(this).attr('data-solarSystemID'),
            current_ids = $('input[name=systems]').val();
        $('input[name=systems]').val(current_ids.replace(solarSystemID, '').replace(',,', ',').replace(/^,|,$/, ''));
        $(this).parent().fadeOut();
        return false;
    });

    // Autocomplete selection of system/region names.
    $('#system-autocomplete').autocomplete({
        source: systemsAndRegions,
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>").append("<a>" + item.label + " (" + item.region + ")</a>").appendTo(ul);
            };
        },
        focus: function (event, ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
            return false;
        },
        select: function (event, ui) {
            event.preventDefault();
            // Add the selected item to the list below.
            $('.selected-systems').append('<li><a href="#" class="remove-system" data-solarsystemid="' + ui.item.value + '">' + ui.item.label + ' (' + ui.item.region + ')</a></li>');
            $('input[name=systems]').val($('input[name=systems]').val() + ',' + ui.item.value);
            $(this).value = '';
            return false;
        }
    });

});
