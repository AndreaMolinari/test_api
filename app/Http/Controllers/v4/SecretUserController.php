<?php
namespace App\Http\Controllers\v4;

use Str;
use App\Models\{TT_SecretUserModel, TT_UtenteModel};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Symfony\Component\HttpKernel\Exception\{UnprocessableEntityHttpException, NotFoundHttpException};

class SecretUserController
{
    CONST CACHE_KEY="TT_SecretUser";

    public static function get_all()
    {
        return TT_SecretUserModel::all()->load('utente');
    }

    public static function get_id(int $id)
    {
        return TT_SecretUserModel::findOrFail($id)->load('utente');
    }

    public static function create(Request $request, int $id = null)
    {
        // Aggiungo un commento
        $validator = Validator::make($request->all(), [
            'idUtente' => 'required|unique:TT_SecretUser,idUtente'
        ]);

        if( $validator->fails() )
        {
            throw new UnprocessableEntityHttpException('idUtente Richiesto o già presente');
        }

        TT_UtenteModel::findOrFail($request->idUtente);
        $secret = TT_SecretUserModel::make();
        $secret->secret = Str::random(25);
        $secret->idUtente = $request->idUtente;
        $secret->idOperatore = Auth::user()->id;

        $secret->save();

        return $secret;
    }

    public static function delete(int $id)
    {
        $user = TT_SecretUserModel::findOrFail($id);

        return $user->delete();
    }

    public static function get_one(string $param, string $val)
    {
        switch ( $param )
        {
            case "secret":
                $sec = TT_SecretUserModel::firstWhere('secret', $val);
            break;
            case "user":
                $sec = TT_SecretUserModel::firstWhere('idUtente', $val);
            break;
            default:
                throw new NotFoundHttpException('Not found');
            break;
        }
        
        return $sec;
    }

    public static function get_userID(string $secret)
    {
        return static::get_one('secret', $secret)->idUtente;
    }

    static function check_secret(string $secret) 
    {
        return (static::get_one('secret', $secret) instanceof TT_SecretUserModel) ? true : false;
    }
}
?>
