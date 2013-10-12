gMap = google.maps;
mapUtils = {
    toLatLng: function (array) {
        if (array instanceof gMap.LatLng) {
            return array;
        } else if (_.isObject(array) && array.latitude !== undefined) {
            return new gMap.LatLng(array.latitude, array.longitude);
        } else if (_.isArray(array)) {
            return new gMap.LatLng(array[0], array[1]);
        }
        return new gMap.LatLng(1, 1);
    },
    latLngToArray: function (latLng) {
        if (_.isArray(latLng)) {
            return latLng;
        } else {
            return [latLng.lat(), latLng.lng()];
        }
    },
    inRadius: function (center, radius, point) {
        var dist = Math.sqrt(Math.pow(center[0] - point[0], 2) + Math.pow(center[1] - point[1], 2));
        return dist <= radius
    },
    zoomTo: function zoomTo(map, stop){
        if (map.getZoom() == stop) {
            return;
        } else {
            console.log(map.getZoom());
            map.setZoom(map.getZoom() + 1);
            setTimeout(mapUtils.zoomTo, 1000, map, stop);
        }
    }
};
map = (function (window, undefined) {
    var options = {
            map: {
                center: new gMap.LatLng(-34.397, 150.644),
                zoom: 8,
                mapTypeControl: false,
                scaleControl: false,
                rotateControl: false,
                streetViewControl: false,
                zoomControl: false,
                mapOverviewControl: false,
                panControl: false,
                mapTypeId: gMap.MapTypeId.ROADMAP
            },
            id: '',
        },
        vermont = [[-71.503554,45.013027],[-71.4926,44.914442],[-71.629524,44.750133],[-71.536416,44.585825],[-71.700724,44.41604],[-72.034817,44.322932],[-72.02934,44.07647],[-72.116971,43.994316],[-72.204602,43.769761],[-72.379864,43.572591],[-72.456542,43.150867],[-72.445588,43.008466],[-72.533219,42.953697],[-72.544173,42.80582],[-72.456542,42.729142],[-73.267129,42.745573],[-73.278083,42.833204],[-73.245221,43.523299],[-73.404052,43.687607],[-73.349283,43.769761],[-73.436914,44.043608],[-73.321898,44.246255],[-73.294514,44.437948],[-73.387622,44.618687],[-73.332852,44.804903],[-73.343806,45.013027],[-72.308664,45.002073],[-71.503554,45.013027]].map(_.compose(mapUtils.toLatLng, function (a) {return a.reverse()})),
        map = null,
        markers = [],
        polylines = [],
        public;

    function config (o) {
        options = _.merge(options, o);
        options.map.center = mapUtils.toLatLng(options.map.center);
    }

    function init (id) {
        map = new google.maps.Map(document.getElementById(options.id), options.map);
        var vt = new gMap.Polygon({path: vermont});
        map.fitBounds(vt.getBounds());
        blackout();
        return public;
    }

    //Method adds a marker to the map
    function addMarker (options) {
        var position = mapUtils.toLatLng(options.position),
            marker = new gMap.Marker({map: map}),
            markerOptions = {
                position: position,
                icon: options.icon || undefined
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
        getMarkerByID(id).setOptions({map: null});
        markers.splice(i, 1);
        return public;
    }

    function getMarkerByID (id) {
        return markers[_.findIndex(markers, {_id: id})];
    }

    function getMarkerPosition (id) {
        return getMarkerByID(id).getPosition();
    }

    function updateMarker (id, pos) {
        getMarkerByID(id).setOptions({position: mapUtils.toLatLng(pos)});
        return public;
    }
    
    //Add Polyline
    function addPolyline (o) {
        var path = o.path.map(mapUtils.toLatLng),
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

    //Delete Polyline
    function deletePolyline (id) {
        var i = _.findIndex(polylines, {_id: id});
        polylines[i] && polylines[i].setOptions({map: null});
        polylines.splice(i, 1);
        return public;
    }

    function blackout () {
        var everything = [[0, -90],[0, 90],[90, -90],[90, 90]].map(mapUtils.toLatLng);
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
        updateMarker: updateMarker,
        getMarkerPosition: getMarkerPosition,
        addPolyline: addPolyline,
        deletePolyline: deletePolyline,
        getCenter: function () {
            return map.getCenter();
        },
        kmlToJson: function (s) {
            return s.split(' ').map(function (s) {
                return s.split(',');
            });
        },
        getMap: function () {
            return map;
        },
    }, public;
}(window));

directions = (function (window, undefined) {
    var d = window.document;
    function directions (/*start, ...positions, onMove*/) {
        var args = _.toArray(arguments),
            onMove = _.last(args),
            all = _.map(_.head(args, args.length - 1), mapUtils.toLatLng),
            start = _.head(all),
            destination = _.last(all),
            waypoints = _.map(_.head(_.rest(all), all.length - 2), toWaypoint),
            request = {
                origin: start,
                destination: destination,
                waypoints: waypoints,
                travelMode: gMap.TravelMode.DRIVING
            },
            directionsService = new google.maps.DirectionsService();

        directionsService.route(request, function (result, status) {
            if (status == gMap.DirectionsStatus.OK) {
                directionEvent(result, onMove);
            }
        });
    }

    function directionEvent (result, onMove) {
        var turnBy = _.flatten(drawDirections(result), true),
            start = true,
            animating = false,
            watch = function (position) {
                var current = _.head(turnBy);
                if (nextDirection || false/*met next point*/) {
                    turnBy = _.rest(turnBy);
                    nextDirection = false;
                    onMove(map.getMarkerPosition(car));
                } else {
                    d.getElementById('directions').innerHTML = current[2];
                    map.updateMarker(car, current[1]);
                    if (start) {
                        setTimeout(function () {
                            map.getMap().panTo(mapUtils.toLatLng(current[1]));
                            map.getMap().setZoom(10);
                            start = false;
                        }, 1000);
                    } else {
                        map.getMap().panTo(mapUtils.toLatLng(current[1]));
                    }
                }
            },
            car = map.addMarker({position: turnBy[0][1], icon: 'http://fc09.deviantart.net/fs70/f/2011/237/8/c/free_cow_icon_by_cg_icons-d47tjp7.gif'});
        setInterval(watch, 10);
        window.navigator.geolocation.watchPosition(watch, null, {enableHighAccuracy: true});
    }

    function drawDirections (result) {
        directions = map.addPolyline({path: result.routes[0].overview_path});
        return _.map(result.routes[0].legs, parseLeg);
    }

    function parseLeg (leg) {
        return _.map(leg.steps, parseStep);
    }

    var stepTemplate = _.template('<var><%= distance.text %></var><strong><%= duration.text %></strong><blockquote><%= instructions %></blockquote>');

    function parseStep (step) {
        return [step.end_point, step.start_point, stepTemplate(step)];
    }

    function toWaypoint (w) {
        return {
            location: mapUtils.toLatLng(w),
            stopover: true
        };
    }

    return  {
        get: directions
    }
}(window));
