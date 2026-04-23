-- Migration to add geofencing area name to funcionarios table
ALTER TABLE ponto.funcionarios
ADD COLUMN area_geofencing VARCHAR(255);

COMMENT ON COLUMN ponto.funcionarios.area_geofencing IS 'Nome da área ou unidade de trabalho vinculada ao geofencing';
