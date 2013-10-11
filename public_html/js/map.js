var map  = (function (window, undefined) {
    var gMap = google.maps,
        options = {
            map: {
                center: new gMap.LatLng(-34.397, 150.644),
                zoom: 8,
                mapTypeId: gMap.MapTypeId.ROADMAP
            },
            id: '',
        },
        map = null,
        markers = [],
        polylines = [],
        public;

    function config (o) {
        options = _.merge(options, o);
        options.map.center = arrayToLatLng(options.map.center);
    }

    function init (id) {
        map = new google.maps.Map(document.getElementById(options.id), options.map);
        blackout();
        return public;
    }

    //Method adds a marker to the map
    function addMarker (options) {
        var position = arrayToLatLng(options.position),
            marker = new gMap.Marker({map: map}),
            //icon = this.getIcon(options.propertyType),
            markerOptions = {
                position: position/*,
                icon: icon.d*/
            };

        marker.setOptions(markerOptions);

        //generate uniqueID
        marker._id = _.uniqueId('marker');

        /*
        gMap.event.addListener(marker, 'mouseover', function () {
            marker.setOptions({icon: icon.h});
        });
        gMap.event.addListener(marker, 'mouseout', function () {
            marker.setOptions({icon: icon.d});
        });
        */

        markers.push(marker);
        return marker._id;
    }

    //Delete Marker
    function deleteMarker (id) {
        var i = _.findIndex(markers, {_id: id});
        markers[i] && markers[i].setOptions({map: null});
        markers.splice(i, 1);
        return public;
    }
    
    //Add Polyline
    function addPolyline (o) {
        var path = o.path.map(arrayToLatLng),
            options = {
               map: map,
               path: path,
               strokeWeight: 2,
               strokeColor: '#000'
            },
            polyline = new gMap.Polyline(options);
        polylines.push(polyline);
        polyline._id = _.uniqueId('polyline');
        return polyline._id;
    }

    function arrayToLatLng (array) {
        if (array instanceof gMap.LatLng) {
            return array;
        }
        return new gMap.LatLng(array[0], array[1]);
    }

    //Delete Polyline
    function deletePolyline (id) {
        var i = _.findIndex(polylines, {_id: id});
        polylines[i] && polylines[i].setOptions({map: null});
        polylines.splice(i, 1);
        return public;
    }

    function blackout () {
        var everything = [[0, -90],[0, 90],[90, -90],[90, 90]].map(arrayToLatLng),
            vermont = [[-71.503554,45.013027],[-71.4926,44.914442],[-71.629524,44.750133],[-71.536416,44.585825],[-71.700724,44.41604],[-72.034817,44.322932],[-72.02934,44.07647],[-72.116971,43.994316],[-72.204602,43.769761],[-72.379864,43.572591],[-72.456542,43.150867],[-72.445588,43.008466],[-72.533219,42.953697],[-72.544173,42.80582],[-72.456542,42.729142],[-73.267129,42.745573],[-73.278083,42.833204],[-73.245221,43.523299],[-73.404052,43.687607],[-73.349283,43.769761],[-73.436914,44.043608],[-73.321898,44.246255],[-73.294514,44.437948],[-73.387622,44.618687],[-73.332852,44.804903],[-73.343806,45.013027],[-72.308664,45.002073],[-71.503554,45.013027]].map(_.compose(arrayToLatLng, function (a) {return a.reverse()}));
        return new gMap.Polygon({
            paths: [everything, vermont],
            strokeColor: "#bbb",
            strokeOpacity: 0.2,
            strokeWeight: 4,
            fillColor: "#bbb",
            fillOpacity: .9,
            map: map
        });
    }

    return public = {
        init: init,
        config: config,
        addMarker: addMarker,
        deleteMarker: deleteMarker,
        addPolyline: addPolyline,
        deletePolyline: deletePolyline,
        getCenter: function () {
            return map.getCenter();
        },
        kmlToJson: function (s) {
            return s.split(' ').map(function (s) {
                return s.split(',');
            });
        }
    }, public;
}(window));
