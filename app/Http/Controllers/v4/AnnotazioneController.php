<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_AnnotazioneModel};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnotazioneController extends Controller
{
    public static function sync(string $tabella, int $idRiferimento, $specific_request = null)
    {
        $stored = TT_AnnotazioneModel::where('tabella', $tabella)->where('idRiferimento', $idRiferimento)->get();
        foreach($stored as $old)
        {
            $found = false;
            if( !is_null($specific_request) )
            {
                foreach($specific_request as $req)
                {
                    if(isset($req['id']) && $old->id == $req['id']) $found = true;
                }
            }
            if(!$found)
            {
                $old->delete();
            }
        }
        $note = [];
        if( is_null($specific_request) ) return $note;
        foreach($specific_request as $req)
        {
            $req['tabella']       = $tabella;
            $req['idRiferimento'] = $idRiferimento;
            $req['idOperatore'] = Auth::user()->id;

            if( isset($req['id']) )
            {
                $note[] = TT_AnnotazioneModel::updateOrCreate(['id' => $req['id']], $req);
            }else{
                $note[] = TT_AnnotazioneModel::updateOrCreate($req);
            }
        }
        return $note;
    }
}
