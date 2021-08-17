/*
 * Original file plugin https://br.wordpress.org/plugins/agile-store-locator/
 * 
 */
var oc_engine = {};

(function($, app_engine) {
  'use strict';

  function codeAddress(_address, _callback) {

    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({ 'address': _address }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        _callback(results[0].geometry);
      } else {
        console.log(status);
      }
    });
  };
  function isEmpty(obj) {

    if (obj == null) return true;
    if (typeof(obj) == 'string' && obj == '') return true;
    return Object.keys(obj).length === 0;
  };

  // Asynchronous load
  var map,
    map_object = {
      is_loaded: true,
      marker: null,
      changed: false,
      store_location: null,
      map_marker: null,
      intialize: function(_callback) {

        var API_KEY = '';
        if (oc_configs && oc_configs.api_key) {
          API_KEY = '&key=' + oc_configs.api_key;
        }

        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = '//maps.googleapis.com/maps/api/js?libraries=places,drawing&' +
          'callback=oc_map_intialized' + API_KEY;
        //+'callback=oc_map_intialized';
        document.body.appendChild(script);
        this.cb = _callback;
      },
      render_a_map: function(_lat, _lng) {

        var hdlr = this,
          map_div = document.getElementById('map_canvas'),
          _draggable = true;

        hdlr.store_location = (_lat && _lng) ? [parseFloat(_lat), parseFloat(_lng)] : [-37.815, 144.965];

        var latlng = new google.maps.LatLng(hdlr.store_location[0], hdlr.store_location[1]);

        if (!map_div) return false;

        var mapOptions = {
          zoom: 14,
          minZoom: 8,
          center: latlng,
          //maxZoom: 10,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          styles: [{ "stylers": [{ "saturation": -100 }, { "gamma": 1 }] }, { "elementType": "labels.text.stroke", "stylers": [{ "visibility": "off" }] }, { "featureType": "poi.business", "elementType": "labels.text", "stylers": [{ "visibility": "off" }] }, { "featureType": "poi.business", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "poi.place_of_worship", "elementType": "labels.text", "stylers": [{ "visibility": "off" }] }, { "featureType": "poi.place_of_worship", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "road", "elementType": "geometry", "stylers": [{ "visibility": "simplified" }] }, { "featureType": "water", "stylers": [{ "visibility": "on" }, { "saturation": 50 }, { "gamma": 0 }, { "hue": "#50a5d1" }] }, { "featureType": "administrative.neighborhood", "elementType": "labels.text.fill", "stylers": [{ "color": "#333333" }] }, { "featureType": "road.local", "elementType": "labels.text", "stylers": [{ "weight": 0.5 }, { "color": "#333333" }] }, { "featureType": "transit.station", "elementType": "labels.icon", "stylers": [{ "gamma": 1 }, { "saturation": 50 }] }]
        };

        hdlr.map_instance = map = new google.maps.Map(map_div, mapOptions);

        // && navigator.geolocation && _draggable
        if ((!hdlr.store_location || isEmpty(hdlr.store_location[0]))) {

          /*navigator.geolocation.getCurrentPosition(function(position){
          	
          	hdlr.changed = true;
          	hdlr.store_location = [position.coords.latitude,position.coords.longitude];
          	var loc = new google.maps.LatLng(position.coords.latitude,  position.coords.longitude);
          	hdlr.add_marker(loc);
          	map.panTo(loc);
          });*/

          hdlr.add_marker(latlng);
        } else if (hdlr.store_location) {
          if (isNaN(hdlr.store_location[0]) || isNaN(hdlr.store_location[1])) return;
          //var loc = new google.maps.LatLng(hdlr.store_location[0], hdlr.store_location[1]);
          hdlr.add_marker(latlng);
          map.panTo(latlng);
        }
      },
      add_marker: function(_loc) {

        var hdlr = this;

        hdlr.map_marker = new google.maps.Marker({
          draggable: true,
          position: _loc,
          map: map
        });

        var marker_icon = new google.maps.MarkerImage('http://maps.google.com/mapfiles/ms/icons/red-dot.png');
        marker_icon.size = new google.maps.Size(24, 39);
        marker_icon.anchor = new google.maps.Point(24, 39);
        hdlr.map_marker.setIcon(marker_icon);
        hdlr.map_instance.panTo(_loc);

        google.maps.event.addListener(
          hdlr.map_marker,
          'dragend',
          function() {

            hdlr.store_location = [hdlr.map_marker.position.lat(), hdlr.map_marker.position.lng()];
            hdlr.changed = true;
            var loc = new google.maps.LatLng(hdlr.map_marker.position.lat(), hdlr.map_marker.position.lng());
            //map.setPosition(loc);
            map.panTo(loc);

            app_engine.pages.store_changed(hdlr.store_location);
          });

      }
    };
  app_engine['pages'] = {
   
    store_changed: function(_position) {

      $('#asl_txt_lat').val(_position[0]);
      $('#asl_txt_lng').val(_position[1]);
    },
    /**
     * [add_store description]
     * @param {[type]} _store   [description]
     */
    add_store: function(_store) {

      var $form = $('#form-store'),
          hdlr  = this;
      // Initialize the Google Maps
      window['oc_map_intialized'] = function() {
        if (_store)
          map_object.render_a_map(_store.lat, _store.lng);
        else
          map_object.render_a_map(parseFloat(oc_configs.default_lat), parseFloat(oc_configs.default_lng));
      };

      if (!(window['google'] && google.maps)) {
        map_object.intialize();
      } else
        oc_map_intialized();
      //  To get Lat/lng
      $('#txt_city,#txt_state,#txt_postal_code').bind('blur', function(e) {

        if (!isEmpty($form[0].elements["city"].value) && !isEmpty($form[0].elements["postal_code"].value)) {

          var address = [$form[0].elements["street"].value, $form[0].elements["city"].value, $form[0].elements["postal_code"].value, $form[0].elements["state"].value];

          var q_address = [];

          for (var i = 0; i < address.length; i++) {

            if (address[i])
              q_address.push(address[i]);
          }

          var _country = jQuery('#txt_country option:selected').text();

          //Add country if available
          if (_country) {
            q_address.push(_country);
          }

          address = q_address.join(', ');

          codeAddress(address, function(_geometry) {

            var s_location = [_geometry.location.lat(), _geometry.location.lng()];
            var loc = new google.maps.LatLng(s_location[0], s_location[1]);
            map_object.map_marker.setPosition(_geometry.location);
            map.panTo(_geometry.location);
            map.setZoom(14);
            app_engine.pages.store_changed(s_location);

          });
        }
      });
      //  Coordinates Fixes
      var _coords = {
        lat: '',
        lng: ''
      };

      //  Click the Edit Coordinates
      $('#lnk-edit-coord').bind('click', function(e) {

        _coords.lat = $('#asl_txt_lat').val();
        _coords.lng = $('#asl_txt_lng').val();

        $('#asl_txt_lat,#asl_txt_lng').val('').removeAttr('readonly');
      });


      //  Change Event Coordinates
      var $coord = $('#asl_txt_lat,#asl_txt_lng');
      $coord.bind('change', function(e) {

        if ($coord[0].value && $coord[1].value && !isNaN($coord[0].value) && !isNaN($coord[1].value)) {

          var loc = new google.maps.LatLng(parseFloat($('#asl_txt_lat').val()), parseFloat($('#asl_txt_lng').val()));
          map_object.map_marker.setPosition(loc);
          map.panTo(loc);
        }
      });
     },
 
  };

})(jQuery, oc_engine);