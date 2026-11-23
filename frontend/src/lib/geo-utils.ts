/**
 * geo-utils.ts
 * دوال مساعدة لحساب الحدود الجغرافية والتسلسل الهرمي للبلديات والأحياء في دمشق
 */

export type LngLatBoundsLike = [[number, number], [number, number]];

// A label wholly inside a longitude band only needs the polygons touching that band.
// Features crossing bands use the complete geometry, including holes and multipart areas.
export function labelAreaMask(areas: GeoJSON.Feature<GeoJSON.Polygon | GeoJSON.MultiPolygon>[]) {
  const polygons = areas.flatMap(({ geometry }) =>
    geometry.type === "Polygon" ? [geometry.coordinates] : geometry.coordinates
  ).filter(polygon => polygon[0]?.length >= 4);
  const within = (coordinates: GeoJSON.Position[][][]) =>
    ["within", { type: "MultiPolygon", coordinates }];
  const fullMask = within(polygons);
  if (!polygons.length) return false;
  if (polygons.length === 1) return fullMask;

  const bounds = polygons.map(coordinates => getFeatureBounds({ geometry: { coordinates } })!);
  const west = Math.min(...bounds.map(b => b[0][0]));
  const east = Math.max(...bounds.map(b => b[1][0]));
  const south = Math.min(...bounds.map(b => b[0][1])) - 0.001;
  const north = Math.max(...bounds.map(b => b[1][1])) + 0.001;
  const rectangle = (w: number, e: number) => within([[[[w, south], [e, south], [e, north], [w, north], [w, south]]]]);
  const bands: unknown[] = ["case"];
  for (let i = 0; i < 8; i++) {
    const w = west + (east - west) * i / 8;
    const e = west + (east - west) * (i + 1) / 8;
    const nearby = polygons.filter((_, j) => bounds[j][0][0] <= e && bounds[j][1][0] >= w);
    bands.push(rectangle(w, e), nearby.length > 0 && within(nearby));
  }
  return ["all", rectangle(west - 0.001, east + 0.001), [...bands, fullMask]];
}

/**
 * حساب الحدود الجغرافية الدقيقة لأي مضلع أو مضلع متعدد (Bounding Box)
 */
export function getFeatureBounds(feature: any): LngLatBoundsLike | null {
  if (!feature || !feature.geometry) return null;
  let minLng = Infinity, minLat = Infinity, maxLng = -Infinity, maxLat = -Infinity;

  function scan(coords: any) {
    if (!Array.isArray(coords)) return;
    if (typeof coords[0] === 'number' && typeof coords[1] === 'number') {
      const [lng, lat] = coords;
      if (lng < minLng) minLng = lng;
      if (lat < minLat) minLat = lat;
      if (lng > maxLng) maxLng = lng;
      if (lat > maxLat) maxLat = lat;
    } else {
      for (const item of coords) {
        scan(item);
      }
    }
  }

  scan(feature.geometry.coordinates);
  if (minLng === Infinity || maxLng === -Infinity) return null;
  return [[minLng, minLat], [maxLng, maxLat]];
}

/**
 * استخراج الأحياء التابعة لبلدية معينة من الـ GeoJSON
 */
export function getChildNeighborhoods(features: any[], municipalityId: number): any[] {
  if (!Array.isArray(features)) return [];
  return features.filter(
    (f) => Number(f.properties?.level) === 10 && Number(f.properties?.parent_id) === Number(municipalityId)
  );
}

/**
 * استخراج بيانات البلدية بواسطة معرفها ID
 */
export function getMunicipalityById(features: any[], municipalityId: number): any | null {
  if (!Array.isArray(features)) return null;
  return (
    features.find(
      (f) => Number(f.properties?.level) === 9 && Number(f.properties?.id) === Number(municipalityId)
    ) || null
  );
}
