import math
import re
from functools import lru_cache

from .models import Street


def _distance(a, b):
    lon1, lat1 = math.radians(a[0]), math.radians(a[1])
    lon2, lat2 = math.radians(b[0]), math.radians(b[1])
    dlat, dlon = lat2 - lat1, lon2 - lon1
    value = math.sin(dlat / 2) ** 2 + math.cos(lat1) * math.cos(lat2) * math.sin(dlon / 2) ** 2
    return 6371000 * 2 * math.asin(min(1, math.sqrt(value)))


def _point_segment_distance(point, a, b):
    lat_scale = 111320
    lon_scale = math.cos(math.radians(point[1])) * 111320
    px, py = (point[0] - a[0]) * lon_scale, (point[1] - a[1]) * lat_scale
    bx, by = (b[0] - a[0]) * lon_scale, (b[1] - a[1]) * lat_scale
    denominator = bx * bx + by * by
    fraction = max(0, min(1, (px * bx + py * by) / denominator)) if denominator else 0
    projected = [a[0] + (b[0] - a[0]) * fraction, a[1] + (b[1] - a[1]) * fraction]
    return _distance(point, projected)


def _geometry_distance(point, geometry):
    if not isinstance(geometry, dict):
        return math.inf
    geometry_type = geometry.get('type')
    coordinates = geometry.get('coordinates') or []
    if geometry_type == 'LineString':
        lines = [coordinates]
    elif geometry_type == 'MultiLineString':
        lines = coordinates
    else:
        return math.inf
    closest = math.inf
    for line in lines:
        for a, b in zip(line, line[1:]):
            if len(a) >= 2 and len(b) >= 2:
                closest = min(closest, _point_segment_distance(point, a, b))
    return closest


@lru_cache(maxsize=2048)
def location_label(lon, lat):
    """Return the nearest Arabic street name for a coordinate."""
    point = [float(lon), float(lat)]

    nearest_street = None
    nearest_street_distance = math.inf
    for street in Street.objects.filter(is_active=True).only(
        'name_ar', 'center_lon', 'center_lat', 'bbox', 'geometry'
    )[:5000]:
        bbox = street.bbox if isinstance(street.bbox, list) and len(street.bbox) == 4 else None
        if bbox and not (bbox[0] - 0.002 <= lon <= bbox[2] + 0.002 and
                         bbox[1] - 0.002 <= lat <= bbox[3] + 0.002):
            continue
        distance = _geometry_distance(point, street.geometry)
        if distance < nearest_street_distance:
            nearest_street_distance = distance
            nearest_street = street
    if nearest_street and nearest_street_distance <= 180:
        return {
            'label': re.sub(r'^شارع\s+', '', nearest_street.name_ar.strip()),
            'kind': 'street',
            'distance_m': round(nearest_street_distance),
        }
    return None


def describe_location(lon, lat, fallback='الموقع المقترح'):
    result = location_label(lon, lat)
    if not result:
        return fallback
    return f"شارع {result['label']}"
