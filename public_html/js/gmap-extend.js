if (!google.maps.Polygon.prototype.getBounds) {
    google.maps.Polygon.prototype.getBounds = function(latLng) {
        var bounds = new google.maps.LatLngBounds(),
            paths = this.getPaths(),
            path,
            i = 0,
            p = 0;
        for (p; p < paths.getLength(); p += 1) {
            path = paths.getAt(p);
            for (i; i < path.getLength(); i += 1) {
                bounds.extend(path.getAt(i));
            }
        }
        return bounds;
    };
}
//based on solutions from http://local.wasp.uwa.edu.au/~pbourke/geometry/polyarea/
if (!google.maps.geometry.poly.getLatLngArea) {
    google.maps.geometry.poly.getLatLngArea = function(p) {
        if (p instanceof google.maps.Polygon || p instanceof google.maps.Polyline) {
            var area = 0,
                pts = p.getPath(),
                n = pts.length,
                i = 0,
                j = n - 1,
                p1,
                p2;

            for (i; i < n; i += 1) {
                p1 = pts.getAt(i);
                p2 = pts.getAt(j);
                area += p1.lat() * p2.lng();
                area -= p1.lng() * p2.lat();
                j = i;
            }

            area = area / 2;

            return area;
        } else {
            throw new TypeError('google.maps.geometry.poly.getLatLngArea: Argument 1 must be an instance of google.maps.Polygon or google.maps.Polyline.');
        }
    };
}
if (!google.maps.geometry.poly.getCentroid) {
    google.maps.geometry.poly.getCentroid = function(p) {
        if (p instanceof google.maps.Polygon || p instanceof google.maps.Polyline) {
            var pts = p.getPath(),
                n = pts.length,
                lat = 0,
                lng = 0,
                i = 0,
                j = n - 1,
                p1,
                p2,
                f;

            for (i; i < n; i += 1) {
                p1 = pts.getAt(i);
                p2 = pts.getAt(j);
                f = p1.lat() * p2.lng() - p2.lat() * p1.lng();
                lat += (p1.lat() + p2.lat()) * f;
                lng += (p1.lng() + p2.lng()) * f;
                j = i;
            }

            f = google.maps.geometry.poly.getLatLngArea(p) * 6;

            return new google.maps.LatLng((lat / f), (lng / f));
        } else {
            throw new TypeError('google.maps.geometry.poly.getCentroid: Argument 1 must be an instance of google.maps.Polygon or google.maps.Polyline.');
        }
    };
}
//quick reference function for polygon.
if (!google.maps.Polygon.prototype.getCentroid) {
    google.maps.Polygon.prototype.getCentroid = function () {
        return google.maps.geometry.poly.getCentroid(this);
    };
}
//quick reference function for polyline.
if (!google.maps.Polyline.prototype.getCentroid) {
    google.maps.Polyline.prototype.getCentroid = function () {
        return google.maps.geometry.poly.getCentroid(this);
    };
}
