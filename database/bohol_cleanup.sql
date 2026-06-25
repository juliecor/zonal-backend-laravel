-- Bohol cleanup for PRODUCTION: uppercase labels + merge duplicate LGU names + dedupe.
-- Run AFTER bohol_missing_import.sql.
START TRANSACTION;
UPDATE zonal_values SET city_municipality=UPPER(city_municipality) WHERE province='BOHOL';
UPDATE zonal_values SET city_municipality='TAGBILARAN CITY'
  WHERE province='BOHOL' AND city_municipality IN ('TAGBILARAN','TAGBILARAN CITY','TAGBILARAN CITY, BOHOL');
UPDATE zonal_values SET city_municipality='GETAFE'
  WHERE province='BOHOL' AND city_municipality IN ('GETAFE/JETAFE','GETAPE');
UPDATE zonal_values SET city_municipality='PRES. CARLOS P. GARCIA'
  WHERE province='BOHOL' AND city_municipality IN ('PRESIDENT GARCIA','PRES. CARLOS P. GARCIA');
DELETE z1 FROM zonal_values z1
  JOIN zonal_values z2
    ON z1.province=z2.province AND z1.city_municipality=z2.city_municipality
   AND z1.barangay=z2.barangay AND z1.street_location<=>z2.street_location
   AND z1.vicinity<=>z2.vicinity AND z1.classification_code<=>z2.classification_code
   AND z1.value_per_sqm=z2.value_per_sqm AND z1.id>z2.id
  WHERE z1.province='BOHOL' AND z1.city_municipality IN ('TAGBILARAN CITY','GETAFE','PRES. CARLOS P. GARCIA');
COMMIT;
