<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\TC_RolesTipologiaModel;

class TC_RolesTipologiaController extends Controller
{
    function __construct(){
    }

    public static function update(Request $request, int $id)
    {
        $check = TC_RolesTipologiaModel::find($id);
        if(!empty($check)){
            $data = (array) $request->all();


            /**
             * @var TT_UtenteModel
            */
            $utenteLogged = Auth::user();

            $data['idOperatore'] = $utenteLogged->id;

            $check->fill($data)->save();

            $success = (object) [];
            $success->$id = True;

            return $success;

        }else{
            $error = (object) [];
            $error->$id = 'id non trovato';
            return $error;
        }
    }

    public function add(Request $request)
    {
        /**
         * @var TT_UtenteModel
        */
        $utenteLogged = Auth::user();
        try{
            $validateData = $this->validate($request,[
                'idTipologia' => 'required',
                'roles' => 'required',
                'bloccato' => 'nullable'
            ]);
        }catch(ValidationException $e){
            return $e->errors();
        }

        $tabella = new TC_RolesTipologiaModel;

        $validateData['idOperatore'] = $utenteLogged->id;

        $ins = $tabella->fill($validateData)->save();

        $id = $tabella->id;
        $success = (object) [];
        $success->$id = True;

        return $success;
    }

    public static function getAll(Request $request = null)
    {
        $results = TC_RolesTipologiaModel::where('bloccato', 0)->get();
        return $results;
    }

    public static function getSingolo(Request $request, $id)
    {
        $result = TC_RolesTipologiaModel::find($id);
        if(!empty($result)){
            return $result;
        }else{
            $error = (object) [];
            $error->$id = 'id non trovato';
            return $error;
        }
        return $result;
    }

    public static function delete($id)
    {
        $result = TC_RolesTipologiaModel::find($id);

        if(!empty($result)){
            $result->delete();

            $success = (object) [];
            $success->$id = True;
            return $success;
        }else{
            $error = (object) [];
            $error->$id = 'id non trovato';
            return $error;
        }
    }

    public function logicRemove($id)
    {
        $check = TC_RolesTipologiaModel::find($id);
                    // ? l'if serve soltanto per l'importazione
            $utenteLogged = (empty(Auth::user())) ? $this->gestiscoIdOperatore($request->idOperatore)  : Auth::user()->dataUtente();

        $dati = [];

        if(!empty($check)){
                        // ? l'if serve soltanto per l'importazione
            $utenteLogged = (empty(Auth::user())) ? $this->gestiscoIdOperatore($request->idOperatore)  : Auth::user()->dataUtente();
            $dati['idOperatore'] = $utenteLogged->id;
            $dati['deleted'] = True;

            $check->fill($dati)->save();

            $success = (object) [];
            $success->$id = True;

            return $success;
        }else{
            $error = (object) [];
            $error->$id = 'id non trovato';
            return $error;
        }
    }

    public function getallcleaned()
    {
        $result = (object) [];

        $tmp = TC_RolesTipologiaModel::select(['Roles', 'idTipologia'])->where('bloccato', 0)->get();

        foreach($tmp as $singolo){
            $id = $singolo->idTipologia;
            $result->$id = $singolo;
        }

        return $result;
    }
}

// {
//     "id": 23,
//     "tipologia": "Admin",
//     "descrizione": "Tipologia amministratore",
//     "idParent": 2,
//     "children": []
//   },
//   {
//     "id": 36,
//     "tipologia": "Principale",
//     "descrizione": "",
//     "idParent": 2,
//     "children": []
//   },
//   {
//     "id": 97,
//     "tipologia": "operatore",
//     "descrizione": null,
//     "idParent": 2,
//     "children": []
//   },
//   {
//     "id": 98,
//     "tipologia": "superadmin",
//     "descrizione": null,
//     "idParent": 2,
//     "children": []
//   },
//   {
//     "id": 99,
//     "tipologia": "superuser",
//     "descrizione": null,
//     "idParent": 2,
//     "children": []
//   },
//   {
//     "id": 100,
//     "tipologia": "user",
//     "descrizione": null,
//     "idParent": 2,
//     "children": []
//   }
