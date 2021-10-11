<?php
//!! DOCUMENTAZIONE 
// ! https://portals.webfleet.com/s/article/WEBFLEET-Essentials?language=it
// TODO: Model creato solo per ricordare dov'è la documentazione

/**
 * In ogni caso penso besti un fe, e qua salvare la soluzione allarme. al limite qualche dato dell'autista.
 * e forse esiste gia la  tipologia per anagrafica uomo centrale
 */

namespace App\Models\CentraleOperativa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="Allarme",
 *     description="Allarme di centrale operativa",
 *     @OA\Xml(
 *         name="Allarme"
 *     )
 * )
 */
class Allarm extends Model {
    use HasFactory;

    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @property integer
     */
    private $id;
}

// non male
// (1,1,2,'servizi-acque','API','Api01!','F92C27F6-93FC-11E3-9EB5-0C43A32B40B3',b'0','2019-01-15 16:16:38'),
// (2,4,2,'acque7','API','Api01!','F92C27F6-93FC-11E3-9EB5-0C43A32B40B3',b'0','2019-01-15 16:16:39');


// !! Guarda sopra!

// bello anche 
// https://developer.tomtom.com/content/search-api-explorer
