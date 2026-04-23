-- Migration to add geofencing fields to funcionarios table
ALTER TABLE ponto.funcionarios
ADD COLUMN lat_permitida NUMERIC(10,8),
ADD COLUMN long_permitida NUMERIC(11,8),
ADD COLUMN distancia_max_permitida INTEGER DEFAULT 200;

COMMENT ON COLUMN ponto.funcionarios.lat_permitida IS 'Latitude permitida para registro de ponto';
COMMENT ON COLUMN ponto.funcionarios.long_permitida IS 'Longitude permitida para registro de ponto';
COMMENT ON COLUMN ponto.funcionarios.distancia_max_permitida IS 'Distância máxima permitida em metros';
