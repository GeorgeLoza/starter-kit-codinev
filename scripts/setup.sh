#!/usr/bin/env bash
set -e

echo "?? CODINEV Starter - Setup"

# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
cd ..

# Web
cd web
pnpm install
cd ..

# Mobile
cd mobile
pnpm install
cd ..

echo "? Setup completado. Revisa backend/.env y configura tu base de datos."
