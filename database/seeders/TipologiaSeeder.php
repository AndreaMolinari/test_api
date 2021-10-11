<?php

namespace Database\Seeders;

use App\Models\v5\Tipologia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TipologiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    const TIPOLOGIE_ESISTENTI = [
        [
            "id" => 1,
            "tipologia" => "Anagrafica",
            "descrizione" => "Anagrafica Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 2,
            "tipologia" => "Utente",
            "descrizione" => "Utente Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 3,
            "tipologia" => "Indirizzo",
            "descrizione" => "Varie tipologie di indirizzo",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 4,
            "tipologia" => "Contatto",
            "descrizione" => "Contatto Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 5,
            "tipologia" => "Fatturazione",
            "descrizione" => "Fatturazione Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 6,
            "tipologia" => "Brand",
            "descrizione" => "Brand Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 7,
            "tipologia" => "Modello",
            "descrizione" => "Modello Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 8,
            "tipologia" => "Mezzo",
            "descrizione" => "Mezzo Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 9,
            "tipologia" => "Componente",
            "descrizione" => "Componente Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 10,
            "tipologia" => "Periferica GPS",
            "descrizione" => "Dispositivo di localizzazione GPS",
            "idParent" => 65,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 11,
            "tipologia" => "Accessorio per periferica",
            "descrizione" => "Accessori accoppiabili con perfieriche GPS",
            "idParent" => 65,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 12,
            "tipologia" => "Clienti",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 13,
            "tipologia" => "Commerciale",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 14,
            "tipologia" => "Officina",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 15,
            "tipologia" => "Operatore",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 16,
            "tipologia" => "Autista",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 17,
            "tipologia" => "Sede Legale",
            "descrizione" => null,
            "idParent" => 3,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 18,
            "tipologia" => "Tipologia",
            "descrizione" => "Tipologia di anagrafica",
            "idParent" => 1,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 19,
            "tipologia" => "Genere",
            "descrizione" => "Ex sesso",
            "idParent" => 1,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 20,
            "tipologia" => "Persona Fisica",
            "descrizione" => null,
            "idParent" => 19,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 21,
            "tipologia" => "Persona Giuridica",
            "descrizione" => null,
            "idParent" => 19,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 22,
            "tipologia" => "Ente pubblico",
            "descrizione" => null,
            "idParent" => 19,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 23,
            "tipologia" => "Admin",
            "descrizione" => "Inserisce Modifica ed Elimina ogni cosa!",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 24,
            "tipologia" => "E-Mail",
            "descrizione" => null,
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 25,
            "tipologia" => "Cellulare",
            "descrizione" => null,
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 26,
            "tipologia" => "Installatore",
            "descrizione" => "Tendenzialmente in officina",
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 27,
            "tipologia" => "Fornitore",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 28,
            "tipologia" => "Automobile",
            "descrizione" => null,
            "idParent" => 6,
            "bloccato" => 0,
            "deleted" => 1,
            "idOperatore" => 1
        ],
        [
            "id" => 29,
            "tipologia" => "Servizio",
            "descrizione" => "Servizio parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 30,
            "tipologia" => "Noleggio",
            "descrizione" => null,
            "idParent" => 91,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 31,
            "tipologia" => "Acquisto",
            "descrizione" => null,
            "idParent" => 91,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 32,
            "tipologia" => "Conto Visione",
            "descrizione" => null,
            "idParent" => 91,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 33,
            "tipologia" => "Omaggio",
            "descrizione" => null,
            "idParent" => 91,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 34,
            "tipologia" => "PEC",
            "descrizione" => null,
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 35,
            "tipologia" => "Autotreno",
            "descrizione" => null,
            "idParent" => 6,
            "bloccato" => 0,
            "deleted" => 1,
            "idOperatore" => 1
        ],
        [
            "id" => 36,
            "tipologia" => "Rivenditore",
            "descrizione" => "Utente con accesso al WebGest, gestisce i suoi sottoclienti e visualizza servizi e flotte",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 37,
            "tipologia" => "Agente",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 38,
            "tipologia" => "Modalita",
            "descrizione" => null,
            "idParent" => 5,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 39,
            "tipologia" => "Cadenza",
            "descrizione" => null,
            "idParent" => 5,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 40,
            "tipologia" => "Rimessa diretta",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 1,
            "idOperatore" => 1
        ],
        [
            "id" => 41,
            "tipologia" => "RID",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 42,
            "tipologia" => "RIBA",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 43,
            "tipologia" => "Contanti",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 44,
            "tipologia" => "Bonifico bancario",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 45,
            "tipologia" => "Corrispettivo pagato",
            "descrizione" => null,
            "idParent" => 38,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 46,
            "tipologia" => "30 gg",
            "data" => [
                "day" => 1,
                "delayMonths" => [1]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 47,
            "tipologia" => "60 gg",
            "data" => [
                "day" => 1,
                "delayMonths" => [2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 48,
            "tipologia" => "90 gg",
            "data" => [
                "day" => 5,
                "delayMonths" => [3]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 49,
            "tipologia" => "60 + 20 gg dffm",
            "data" => [
                "day" => 20,
                "delayMonths" => [2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 50,
            "tipologia" => "Periodicita",
            "descrizione" => null,
            "idParent" => 5,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 51,
            "tipologia" => "Mensile",
            "data" => ['months' => 1],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 52,
            "tipologia" => "Bimestrale",
            "data" => ['months' => 2],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 53,
            "tipologia" => "Trimestrale",
            "data" => ['months' => 3],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 54,
            "tipologia" => "Semestrale",
            "data" => ['months' => 6],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 55,
            "tipologia" => "Annuale",
            "data" => ['months' => 12],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 56,
            "tipologia" => "Sede Operativa",
            "descrizione" => null,
            "idParent" => 3,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 57,
            "tipologia" => "Residenza",
            "descrizione" => null,
            "idParent" => 3,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 58,
            "tipologia" => "GPS",
            "descrizione" => null,
            "idParent" => 6,
            "bloccato" => 0,
            "deleted" => 1,
            "idOperatore" => 1
        ],
        [
            "id" => 59,
            "tipologia" => "Relazioni",
            "descrizione" => "Anagrafica Relazione! TIPOLOGIA LOCKED",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 60,
            "tipologia" => "Sotto Cliente",
            "descrizione" => null,
            "idParent" => 59,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 61,
            "tipologia" => "Automobile",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 62,
            "tipologia" => "Autotreno",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 63,
            "tipologia" => "Furgone",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 64,
            "tipologia" => "Veicoli a motore",
            "descrizione" => null,
            "idParent" => 7,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 65,
            "tipologia" => "Componenti aggiuntivi",
            "descrizione" => null,
            "idParent" => 7,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 66,
            "tipologia" => "Dipendente",
            "descrizione" => null,
            "idParent" => 59,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 67,
            "tipologia" => "FAX",
            "descrizione" => null,
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 68,
            "tipologia" => "Telefono Fisso",
            "descrizione" => null,
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 69,
            "tipologia" => "Triennale",
            "data" => ['months' => 36],
            "idParent" => 50,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 70,
            "tipologia" => "0",
            "descrizione" => "Periodicita fatturazione 0",
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 71,
            "tipologia" => "120 gg dffm",
            "data" => [
                "day" => 1,
                "delayMonths" => [4]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 72,
            "tipologia" => "30 + 10 gg dffm",
            "data" => [
                "day" => 10,
                "delayMonths" => [1]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 73,
            "tipologia" => "30 + 15 gg dffm",
            "data" => [
                "day" => 15,
                "delayMonths" => [1]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 74,
            "tipologia" => "30 + 20 gg dffm",
            "data" => [
                "day" => 20,
                "delayMonths" => [1]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 75,
            "tipologia" => "30 + 5 gg dffm",
            "data" => [
                "day" => 5,
                "delayMonths" => [1]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 76,
            "tipologia" => "30/60 gg dffm",
            "data" => [
                "day" => 1,
                "delayMonths" => [1,2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 77,
            "tipologia" => "30/60/90 gg dffm",
            "data" => [
                "day" => 1,
                "delayMonths" => [1,2,3]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 78,
            "tipologia" => "60 + 10 gg dffm",
            "data" => [
                "day" => 10,
                "delayMonths" => [2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 79,
            "tipologia" => "60 + 15 gg dffm",
            "data" => [
                "day" => 15,
                "delayMonths" => [2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 80,
            "tipologia" => "60 + 5 gg dffm",
            "data" => [
                "day" => 5,
                "delayMonths" => [2]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 81,
            "tipologia" => "60/90 gg dffm",
            "data" => [
                "day" => 1,
                "delayMonths" => [2,3]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 82,
            "tipologia" => "90 + 10 gg dffm",
            "data" => [
                "day" => 10,
                "delayMonths" => [3]
            ],
            "idParent" => 39,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 83,
            "tipologia" => "WebService",
            "descrizione" => null,
            "idParent" => 29,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 84,
            "tipologia" => "Analyzer",
            "descrizione" => "WebService Analyzer",
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 85,
            "tipologia" => "Accise",
            "descrizione" => "WebService Accise",
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 86,
            "tipologia" => "WebTrax",
            "descrizione" => "WebService WebTrax",
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 87,
            "tipologia" => "Joy",
            "descrizione" => null,
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 88,
            "tipologia" => "CarbonTax",
            "descrizione" => null,
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 89,
            "tipologia" => "Tacho",
            "descrizione" => null,
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 90,
            "tipologia" => "WebGest",
            "descrizione" => null,
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 91,
            "tipologia" => "Causale",
            "descrizione" => null,
            "idParent" => 29,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 92,
            "tipologia" => "Tachigrafo",
            "descrizione" => null,
            "idParent" => 65,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 93,
            "tipologia" => "Radiocomando",
            "descrizione" => null,
            "idParent" => 65,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 94,
            "tipologia" => "Semirimorchio",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 95,
            "tipologia" => "Pullman",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 96,
            "tipologia" => "Macchina Operatrice",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 97,
            "tipologia" => "Operatore",
            "descrizione" => "Inserisce e modifica ogni cosa, elimina le flotte",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 98,
            "tipologia" => "SuperAdmin",
            "descrizione" => "Reparto dev, accede anche alle crud",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 99,
            "tipologia" => "Utente Pro",
            "descrizione" => "Utente Trax, vede le sue flotte e le flotte dei sottoutenti. NON Accede allo SCADENZIARIO,  Modifica le Uscite, Gestisce la sua Flotta, Può creare sotto utenti",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 100,
            "tipologia" => "Utente limitato",
            "descrizione" => "Utente Trax Limitato Visualizza la posizione, Genera i report/tracciati per le sue flotte",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 101,
            "tipologia" => "test",
            "descrizione" => "test",
            "idParent" => 13,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 102,
            "tipologia" => "Associazione",
            "descrizione" => "Non hanno partita IVA",
            "idParent" => 19,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 103,
            "tipologia" => "API",
            "descrizione" => null,
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 104,
            "tipologia" => "Manutenzione",
            "descrizione" => "Parent",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 105,
            "tipologia" => "Ordinaria",
            "descrizione" => "Manutenzione programmata",
            "idParent" => 104,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 106,
            "tipologia" => "Straordinaria",
            "descrizione" => null,
            "idParent" => 104,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 107,
            "tipologia" => "Campi Custom Anagrafica",
            "descrizione" => "I campi che consigliamo all'utente in Trax",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 108,
            "tipologia" => "Newsletter",
            "descrizione" => "Mail per l'invio delle newsletters. Saranno poi tutte esportabili a parte.",
            "idParent" => 4,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 109,
            "tipologia" => "Officina",
            "descrizione" => "Officine custom per ogni anagrafica",
            "idParent" => 107,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 110,
            "tipologia" => "Causali",
            "descrizione" => "Tipologie di manutenzione",
            "idParent" => 107,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 111,
            "tipologia" => "Bicicletta",
            "descrizione" => null,
            "idParent" => 64,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 112,
            "tipologia" => "Utente Pro + SCAD",
            "descrizione" => "Utente Trax, vede le sue flotte e le flotte dei sottoutenti. Accede allo SCADENZIARIO,  Modifica le Uscite, Gestisce la sua Flotta, Può creare sotto utenti",
            "idParent" => 2,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 113,
            "tipologia" => "Rivenditore",
            "descrizione" => null,
            "idParent" => 18,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 114,
            "tipologia" => "Custom Email",
            "descrizione" => "Email per manutenzioni",
            "idParent" => 107,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 115,
            "tipologia" => "Soglie",
            "descrizione" => "Tipologia delle soglie per notifiche target",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 116,
            "tipologia" => "Temperatura",
            "descrizione" => "Soglia basata sulla temperatura",
            "idParent" => 115,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 117,
            "tipologia" => "Velocita",
            "descrizione" => "Soglia basata sulla velocità",
            "idParent" => 115,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 118,
            "tipologia" => "Giorno",
            "descrizione" => "Soglia basata sui giorni della settimana",
            "idParent" => 115,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 119,
            "tipologia" => "Ora",
            "descrizione" => "Soglie basata su un orario",
            "idParent" => 115,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 120,
            "tipologia" => "NotificaTarget",
            "descrizione" => "Tipologia delle notifiche dei target",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 121,
            "tipologia" => "In",
            "descrizione" => "Indica l'entrata nella soglia",
            "idParent" => 120,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 122,
            "tipologia" => "Out",
            "descrizione" => "Indica l'uscita nella soglia",
            "idParent" => 120,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 123,
            "tipologia" => "In/Out",
            "descrizione" => "Indica sia l'entrata che l'uscita dalla soglia",
            "idParent" => 120,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 124,
            "tipologia" => "Industria 4.0",
            "descrizione" => "Modula una uscita della periferica quando entri o esci dal bersaglio",
            "idParent" => 120,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 125,
            "tipologia" => "DDT",
            "descrizione" => "Tipologia generica per documenti di trasporto",
            "idParent" => null,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 126,
            "tipologia" => "Aspetto",
            "descrizione" => "",
            "idParent" => 125,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 127,
            "tipologia" => "Anas",
            "descrizione" => "Servizi visibili per ANAS",
            "idParent" => 83,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 128,
            "tipologia" => "Busta",
            "descrizione" => "Aspetto collo DDT",
            "idParent" => 126,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 129,
            "tipologia" => "Pacco",
            "descrizione" => null,
            "idParent" => 126,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 130,
            "tipologia" => "Trasporto",
            "descrizione" => null,
            "idParent" => 125,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ],
        [
            "id" => 131,
            "tipologia" => "Corriere",
            "descrizione" => null,
            "idParent" => 125,
            "bloccato" => 0,
            "deleted" => 0,
            "idOperatore" => 1
        ]
    ];

    public function run()
    {
        foreach (static::TIPOLOGIE_ESISTENTI as $tipologia) {
            Tipologia::firstOrCreate($tipologia);
        }
    }
}
