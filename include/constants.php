<?php
// Роли
const ROLE_ADMIN = 2;
const ROLE_ENGINEER = 5;
const ROLE_MECHANIC = 14;

const ROLES = array(ROLE_ADMIN, ROLE_ENGINEER, ROLE_MECHANIC);
const ROLE_NAMES = array(ROLE_ADMIN => "admin", ROLE_ENGINEER => "engineer", ROLE_MECHANIC => "mechanic");
const ROLE_LOCAL_NAMES = array(ROLE_ADMIN => "Администратор", ROLE_ENGINEER => "Инженер", ROLE_MECHANIC => "Механик");
const ROLE_TWOFACTOR = array(ROLE_ADMIN => 0, ROLE_ENGINEER => 0, ROLE_MECHANIC => 0);

// Типы машин
const MACHINE_TYPE_PRINTERS = 1;
const MACHINE_TYPE_LAMINATORS = 2;
const MACHINE_TYPE_CUTS = 3;
const MACHINE_TYPE_CARS = 4;
const MACHINE_TYPE_LOADERS = 5;
const MACHINE_TYPE_OTHER = 6;

const MACHINE_TYPES = array(MACHINE_TYPE_PRINTERS, MACHINE_TYPE_LAMINATORS, MACHINE_TYPE_CUTS, MACHINE_TYPE_CARS, MACHINE_TYPE_LOADERS, MACHINE_TYPE_OTHER);
const MACHINE_TYPE_NAMES = array(MACHINE_TYPE_PRINTERS => "Печатные машины", MACHINE_TYPE_LAMINATORS => "Ламинаторы", MACHINE_TYPE_CUTS => "Резки", MACHINE_TYPE_CARS => "Автомобили", MACHINE_TYPE_LOADERS => "Погрузчики", MACHINE_TYPE_OTHER => "Другое");

// Валюты
const CURRENCY_RUB = "rub";
const CURRENCY_USD = "usd";
const CURRENCY_EURO = "euro";

// Другое
const ISINVALID = ' is-invalid';
?>