-- Script consolidado para criação das tabelas do sis-ponto
-- Banco de Dados: PostgreSQL
-- Schema: ponto

CREATE SCHEMA IF NOT EXISTS ponto;
SET search_path TO ponto, public;

-- Tabela: cargos
CREATE TABLE IF NOT EXISTS cargos (
    id SERIAL PRIMARY KEY,
    cargo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: horarios
CREATE TABLE IF NOT EXISTS horarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) DEFAULT NULL,
    primeiro_horario VARCHAR(20),
    segundo_horario VARCHAR(20),
    terceiro_horario VARCHAR(20),
    quarto_horario VARCHAR(20),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: justificativas
CREATE TABLE IF NOT EXISTS justificativas (
    id SERIAL PRIMARY KEY,
    justificativa TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: dias_da_semana
CREATE TABLE IF NOT EXISTS dias_da_semana (
    id SERIAL PRIMARY KEY,
    segunda BOOLEAN DEFAULT FALSE,
    terca BOOLEAN DEFAULT FALSE,
    quarta BOOLEAN DEFAULT FALSE,
    quinta BOOLEAN DEFAULT FALSE,
    sexta BOOLEAN DEFAULT FALSE,
    sabado BOOLEAN DEFAULT FALSE,
    domingo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: funcionarios
CREATE TABLE IF NOT EXISTS funcionarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    setor VARCHAR(255),
    matricula VARCHAR(50) UNIQUE,
    carga_horaria VARCHAR(50),
    data_nascimento DATE,
    rg VARCHAR(20),
    cpf VARCHAR(14),
    pis_pasep VARCHAR(20),
    titulo_eleitor VARCHAR(20),
    cartao_sus VARCHAR(20),
    mae VARCHAR(255),
    pai VARCHAR(255),
    celular VARCHAR(20),
    bairro VARCHAR(100),
    rua VARCHAR(255),
    numero VARCHAR(20),
    cidade VARCHAR(100),
    uf VARCHAR(2),
    cep VARCHAR(10),
    estado_civil VARCHAR(50),
    email VARCHAR(255),
    id_cargo INTEGER REFERENCES cargos(id) ON DELETE SET NULL,
    id_horario INTEGER REFERENCES horarios(id) ON DELETE SET NULL,
    sexo VARCHAR(20),
    deficiente BOOLEAN DEFAULT FALSE,
    id_dia_da_semana INTEGER REFERENCES dias_da_semana(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: faltas
CREATE TABLE IF NOT EXISTS faltas (
    id SERIAL PRIMARY KEY,
    id_funcionario INTEGER NOT NULL REFERENCES funcionarios(id) ON DELETE CASCADE,
    id_justificativa INTEGER REFERENCES justificativas(id) ON DELETE SET NULL,
    data DATE NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: registros
CREATE TABLE IF NOT EXISTS registros (
    id SERIAL PRIMARY KEY,
    id_funcionario INTEGER NOT NULL REFERENCES funcionarios(id) ON DELETE CASCADE,
    id_horario INTEGER REFERENCES horarios(id) ON DELETE SET NULL,
    primeiro_ponto VARCHAR(20),
    segundo_ponto VARCHAR(20),
    terceiro_ponto VARCHAR(20),
    quarto_ponto VARCHAR(20),
    atrasou_primeiro_ponto BOOLEAN DEFAULT FALSE,
    atrasou_segundo_ponto BOOLEAN DEFAULT FALSE,
    atrasou_terceiro_ponto BOOLEAN DEFAULT FALSE,
    atrasou_quarto_ponto BOOLEAN DEFAULT FALSE,
    id_falta INTEGER REFERENCES faltas(id) ON DELETE SET NULL,
    data DATE NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    level VARCHAR(50),
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Tabela: ferias
CREATE TABLE IF NOT EXISTS ferias (
    id SERIAL PRIMARY KEY,
    id_funcionario INTEGER NOT NULL REFERENCES funcionarios(id) ON DELETE CASCADE,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
