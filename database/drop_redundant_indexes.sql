-- Drop redundant/duplicate indexes on zonal_values (identical to or left-prefixes of others).
-- Read path is unaffected (covering composites idx_place + idx_zonal_city etc. remain); this only
-- cuts INSERT/UPDATE index-maintenance cost (faster imports) and storage.
--   idx_zonal_prov_city_brgy = exact dup of idx_place(province,city_municipality,barangay)
--   idx_zonal_prov_city      = exact dup of idx_city(province,city_municipality)
--   idx_zonal_province       = redundant left-prefix of idx_place/idx_city
ALTER TABLE zonal_values
  DROP INDEX idx_zonal_prov_city_brgy,
  DROP INDEX idx_zonal_prov_city,
  DROP INDEX idx_zonal_province;
