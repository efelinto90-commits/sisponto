#!/bin/bash

# Função para calcular o tempo
time_command() {
  local start_time=$(date +%s)
  echo "Executando: $1"
  eval $1
  local end_time=$(date +%s)
  local duration=$((end_time - start_time))
  echo "Tempo decorrido para '$1': ${duration}s"
  echo "---------------------------------------------"
}

section() {
  echo ""
  echo "============================================="
  echo "$1"
  echo "============================================="
  echo ""
}

section "Atualizando repositório"
time_command "git pull"

section "Atualizando dependências do backend (Composer)"
cd backend || exit
time_command "yes | sudo composer install"  # Automação do "yes" com sudo
cd ..

section "Atualizando dependências do frontend e construindo projeto"
cd frontend || exit
time_command "sudo npm install"
time_command "sudo npm run build"
time_command "sudo npm install -g serve"
cd ..

section "Processo concluído!"
