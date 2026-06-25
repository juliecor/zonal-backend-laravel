-- Remove leftover junk/duplicate provinces from old pre-import data:
--   'NUEVA VISCAYA' (misspelled dup of NUEVA VIZCAYA - superseded by complete RDO 14 import)
--   'province'      (header-row garbage: city='city_municipality', values 0.00)
DELETE FROM zonal_values WHERE province IN ('NUEVA VISCAYA', 'province');
