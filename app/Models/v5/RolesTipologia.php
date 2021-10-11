<?php 
namespace App\Models\v5;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolesTipologia extends Model{

    protected $connection = 'mysql';

    protected $table = 'TC_RolesTipologia';

    public function tipologia(): BelongsTo{
        return $this->belongsTo(Tipologia::class, 'idTipologia');
    }
}