<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\{UtenteRequest, UtenteAvaliableRequest, LoginRequest};
use App\Models\{TC_UtenteFlottaModel, TT_TipologiaModel, TT_UtenteModel};
use Carbon\Carbon;
use Facades\App\Repositories\iHelpU;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{Auth, DB, Hash};

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // $request->validated();

        $credentials = request(['password', 'username']);

        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json([
                'message' => 'Non autorizzato'
            ], 401);
        }

        $user = $request->user();

        if (isset($user->bloccato) && $user->bloccato == 1) {
            return response()->json(['message' => 'Utente bloccato'], 402);
        }

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if (isset($request->remember_me) && $request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        $accessLogParameters = [
            'idUtente' => $user->id,
            'idWebService' => $request->header()['idwebservice'][0]
        ];

        $requestF = new Request();
        $requestF->headers->set('content-type', 'application/json');
        $requestF->initialize($accessLogParameters);

        // $access = TC_LogController::insert($requestF);

        $response = [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'utente' => null
        ];

        $anagrafica = iHelpU::groupBy((new AnagraficaController)->get_all('short'), 'id');
        $idAnagrafica = $user->idAnagrafica;

        $user['isActia'] = (!empty($user['actiaUser']) && !empty($user['actiaPassword']));
        $user['role'] = $user->getRoleLevel();
        $response['utente'] = $user;
        $response['utente']->name = (array_key_exists($idAnagrafica, $anagrafica) ? (!is_null($anagrafica[$idAnagrafica][0]->ragSoc) ? $anagrafica[$idAnagrafica][0]->ragSoc : $anagrafica[$idAnagrafica][0]->nome . ' ' . $anagrafica[$idAnagrafica][0]->cognome) : null);
        $response['utente']->tipologia = TT_TipologiaModel::findOrFail($response['utente']->idTipologia)->tipologia;

        unset(
            $user['password_dec'],
            $user['actiaMail'],
            $user['actiaUser'],
            $user['actiaPassword'],
            $user['created_at'],
            $user['updated_at'],
            $user['idOperatore'],
            $user['bloccato'],
            $user['idParent']
        );

        return response()->json($response);
    }

    public function logout()
    {
        /**@var TT_UtenteModel */
        $user = Auth::guard('api')->user();
        return $user ? $user->token()->revoke() : false;
    }

    public static function get_all()
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() === 4) {
            return DB::select("SELECT `TT_Utente`.`id`, `TT_Utente`.`username`, `TT_Utente`.`actiaUser`, `TT_Utente`.`idTipologia`, `TT_Utente`.`idAnagrafica`, `TT_Utente`.`bloccato`, `TT_Tipologia`.`tipologia`, `TT_Anagrafica`.`nome`, `TT_Anagrafica`.`cognome`, `TT_Anagrafica`.`ragSoc`
            FROM `TT_Utente`
            INNER JOIN `TT_Tipologia` ON `TT_Utente`.`idTipologia` = `TT_Tipologia`.`id`
            INNER JOIN `TT_Anagrafica` ON `TT_Utente`.`idAnagrafica` = `TT_Anagrafica`.`id`
            INNER JOIN `TC_AnagraficaAnagrafica` ON `TT_Anagrafica`.`id` = `TC_AnagraficaAnagrafica`.`idChild`
            WHERE 1
            AND `TC_AnagraficaAnagrafica`.`idParent` = " . $user->idAnagrafica . " ORDER BY `TT_Utente`.`id` DESC;", [1]);
        } else {
            return DB::select('SELECT `TT_Utente`.`id`, `TT_Utente`.`username`, `TT_Utente`.`actiaUser`, `TT_Utente`.`idTipologia`, `TT_Utente`.`idAnagrafica`, `TT_Utente`.`bloccato`, `TT_Tipologia`.`tipologia`, `TT_Anagrafica`.`nome`, `TT_Anagrafica`.`cognome`, `TT_Anagrafica`.`ragSoc` FROM `TT_Utente` INNER JOIN `TT_Tipologia` ON `TT_Utente`.`idTipologia` = `TT_Tipologia`.`id` LEFT OUTER JOIN `TT_Anagrafica` ON `TT_Utente`.`idAnagrafica` = `TT_Anagrafica`.`id` WHERE 1 ORDER BY `TT_Utente`.`id` DESC;', [1]);
        }
    }

    public static function get_id(int $id)
    {
        return TT_UtenteModel::findOrFail($id)->load(['anagrafica', 'tipologia']);
    }

    public static function username_available(UtenteAvaliableRequest $request)
    {
        $request->validated();

        return ['avaliable' => true];
    }

    public static function create(UtenteRequest $request, int $id = null)
    {
        $req = $request->validated();
        $req['idOperatore'] = Auth::id();
        unset($req['password_confirmation']);

        $req['password_dec'] = $req['password'];
        $req['password']     = Hash::make($req['password_dec']);

        if (is_null($id)) {
            $utente = TT_UtenteModel::create($req);
        } else {
            $utente = TT_UtenteModel::find($id);
            $utente->update($req);
        }

        return static::get_id($utente->id);
    }

    public static function delete(int $id)
    {
        $utente = TT_UtenteModel::findOrFail($id);

        $figli = TT_UtenteModel::where('idParent', $id)->get();
        $flotte = TC_UtenteFlottaModel::where('idUtente', $id)->get();

        $relazioni = [$figli, $flotte];
        foreach ($relazioni ?? [] as $relaz) {
            foreach ($relaz ?? [] as $rel) {
                $rel->delete();
            }
        }

        return $utente->delete();
    }

    public function authCheck()
    {
        return Auth::check();
    }
}
