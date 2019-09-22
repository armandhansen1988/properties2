var geocoder;
var map;
var lat = "";
var lng = "";

$(document).ready(function (e) {
    setTimeout("initAutocomplete()", 2000);
});

function initMap()
{
    geocoder = new google.maps.Geocoder();
    if (lat != "" && lng != "") {
        var latlng = new google.maps.LatLng(lat, lng);
        var myOptions = {
            zoom: 20,
            center: latlng,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            },
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map"), myOptions);
        var marker = new google.maps.Marker({
            position: latlng,
            map: map
        });
    } else {
        var latlng = new google.maps.LatLng(7.1007414, 20.6134654);
        var myOptions = {
            zoom: 2,
            center: latlng,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            },
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map"), myOptions);
    }
    
    if (action_type == "edit") {
        doGeocode(address);
    }
}

function initAutocomplete()
{
    autocomplete = new google.maps.places.Autocomplete((document.getElementById('autocomplete')),{types: ['geocode']});
    
    autocomplete.addListener('place_changed', function () {
        var displayable_address = $('#autocomplete').val();
        var street_number, route, area, administrative_area_level_1, country, postcode;
        var place = autocomplete.getPlace();
        var coordinates = autocomplete.getPlace().geometry.location;
        var address_components_length = place.address_components.length;
        for (var i = 0; i < address_components_length; i++) {
            if (place.address_components[i].types[0] == "street_number") {
                street_number = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[0] == "route") {
                route = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[0] == "locality" || place.address_components[i].types[0] == "postal_town") {
                area = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[0] == "administrative_area_level_1") {
                administrative_area_level_1 = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[0] == "country") {
                country = place.address_components[i].long_name;
            }
            if (place.address_components[i].types[0] == "postal_code") {
                postcode = place.address_components[i].long_name;
            }
        }
        $('#town').val(area);
        $('#county').val(administrative_area_level_1);
        $('#country').val(country);
        $('#postcode').val(postcode);
        $('#displayable_address').val(displayable_address);
        $('#coordinates').val(coordinates.lat()+", "+coordinates.lng());
    });

}

function doGeocode(address)
{
    if (geocoder) {
        geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                    map.setCenter(results[0].geometry.location);
                    map.setZoom(20);
                    var infowindow = new google.maps.InfoWindow({
                        content: '<b>' + address + '</b>',
                        size: new google.maps.Size(150, 50)
                    });
    
                    var marker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        title: address
                    });
                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.open(map, marker);
                    });
                } else {
                    alert("No results found");
                }
            } else {
                alert("Unable to find address to display");
            }
        });
    }
}