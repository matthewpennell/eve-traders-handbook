$(document).ready(function () {

    // Clicking an item in the table loads the details via Ajax.
    $('.losses tbody a').click(function () {
        $('.details').html('<img src="i/loader.gif" class="loader">');
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
        $('.details').html('<img src="i/loader.gif" class="loader">');
        $.get('details/' + typeID, {
            me: this.value
        }, function (data) {
            $('.details').html(data);
        });
    });

    // Clicking the button to create a new saved fitting.
    $('#add_new_fit').click(function () {
        $('.new-fittings').append('<div class="fitting"><textarea name="fit_new_' + Math.floor((Math.random() * 1000000) + 1) + '" placeholder="Paste your EFT fitting here..."></textarea></div>');
    });

    // Hovering a fitting label returns a list of the fitting(s) that the item belongs to.
    $('.fits').tooltip({
        content: function (callback) {
            $.get('/fits/item/' + $(this).data('id'), function (fittings) {
                callback(fittings);
            });
        },
        tooltipClass: 'fit-tt',
        track: true
    });

    // Removing a region ID from the list.
    $('body').delegate('.remove-region', 'click', function () {
        var regionID = $(this).attr('data-regionID'),
            current_ids = $('input[name=regions]').val();
        $('input[name=regions]').val(current_ids.replace(regionID, '').replace(',,', ',').replace(/^,|,$/, ''));
        $(this).parent().fadeOut();
        return false;
    });

    // Removing an alliance ID from the list.
    $('body').delegate('.remove-alliance', 'click', function () {
        var allianceID = $(this).attr('data-allianceID'),
            current_ids = $('input[name=alliances]').val();
        $('input[name=alliances]').val(current_ids.replace(allianceID, '').replace(',,', ',').replace(/^,|,$/, ''));
        $(this).parent().fadeOut();
        return false;
    });

    // Autocomplete selection of region names.
    $('#region-autocomplete').autocomplete({
        minLength: 2,
        source: 'settings/regions',
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
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
            $('.selected-regions').append('<li><a href="#" class="remove-region" data-regionid="' + ui.item.value + '">' + ui.item.label + '</a></li>');
            $('input[name=regions]').val($('input[name=regions]').val() + ',' + ui.item.value);
            $(this).value = '';
            return false;
        }
    });

    // Autocomplete selection of alliance names.
    $('#alliance-autocomplete').autocomplete({
        minLength: 2,
        source: 'settings/alliances',
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
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
            $('.selected-alliances').append('<li><a href="#" class="remove-alliance" data-allianceid="' + ui.item.value + '">' + ui.item.label + '</a></li>');
            $('input[name=alliances]').val($('input[name=alliances]').val() + ',' + ui.item.value);
            $(this).value = '';
            return false;
        }
    });

    // Autocomplete selection of region names.
    $('#home-region-autocomplete').autocomplete({
        minLength: 2,
        source: 'settings/regions',
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
            };
        },
        focus: function (event, ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
            return false;
        },
        select: function (event, ui) {
            event.preventDefault();
            $('input[name=home_region_id]').val(ui.item.value);
            return false;
        }
    });

    // Autocomplete selection of home station name based on home region.
    $('#home-station-autocomplete').autocomplete({
        minLength: 2,
        source: 'settings/stations',
        create: function () {
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li>").append("<a>" + item.label + "</a>").appendTo(ul);
            };
        },
        focus: function (event, ui) {
            event.preventDefault();
            $(this).val(ui.item.label);
            return false;
        },
        select: function (event, ui) {
            event.preventDefault();
            $('input[name=home_station_id]').val(ui.item.value);
            return false;
        }
    });

    // Make the API link work.
    $('.show-api').click(function () {
        $('.api-needed').remove();
    });

});
