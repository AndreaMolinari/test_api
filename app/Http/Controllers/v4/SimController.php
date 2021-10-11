<?php
namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TT_ComponenteModel, TT_ServizioModel, TT_SimModel};
use App\Http\Requests\SimRequest;
use Illuminate\Support\Facades\DB;

class SimController extends Controller
{
    public function get_all()
    {
        $rows = DB::select('
        SELECT `TT_Sim`.`id`, `idModello`, `serial`, `apn`, `dataAttivazione`, `dataDisattivazione`, `TT_Brand`.`marca`, `TT_Modello`.`modello`
            FROM `TT_Sim`
            LEFT OUTER JOIN `TT_Modello` ON `TT_Sim`.`idModello` = `TT_Modello`.`id`
            LEFT OUTER JOIN `TT_Brand` ON `TT_Modello`.`id` = `TT_Brand`.`id`
            WHERE 1;
        ');
        return $rows;
    }

    public function get_id(int $id)
    {
        return TT_SimModel::findOrFail($id);
    }

    public function get_unassociated(string $params = null, int $id = null)
    {
        switch( $params )
        {
            case "servizio":
                TT_ServizioModel::findOrFail($id);
                $rows = DB::select('
                    SELECT `TT_Sim`.`id`, `idModello`, `serial`, `apn`, `dataAttivazione`, `dataDisattivazione`, `TT_Brand`.`marca`, `TT_Modello`.`modello`
                    FROM `TT_Sim`
                    LEFT OUTER JOIN `TT_Modello` ON `TT_Sim`.`idModello` = `TT_Modello`.`id`
                    LEFT OUTER JOIN `TT_Brand` ON `TT_Modello`.`id` = `TT_Brand`.`id`
                    WHERE 1 AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Componente WHERE idSim IS NOT NULL
                    ) AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Servizio INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Servizio`.`id` WHERE idSim IS NOT NULL AND (`dataFine` >= now() OR `dataFine` IS NULL) AND TT_Servizio.id != '.$id.'
                    );
                ');
            break;
            case "componente":
                TT_ComponenteModel::findOrFail($id);
                $rows = DB::select('
                    SELECT `TT_Sim`.`id`, `idModello`, `serial`, `apn`, `dataAttivazione`, `dataDisattivazione`, `TT_Brand`.`marca`, `TT_Modello`.`modello`
                    FROM `TT_Sim`
                    LEFT OUTER JOIN `TT_Modello` ON `TT_Sim`.`idModello` = `TT_Modello`.`id`
                    LEFT OUTER JOIN `TT_Brand` ON `TT_Modello`.`id` = `TT_Brand`.`id`
                    WHERE 1 AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Componente WHERE idSim IS NOT NULL AND id != '.$id.'
                    ) AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Servizio INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Servizio`.`id` WHERE idSim IS NOT NULL AND (`dataFine` >= now() OR `dataFine` IS NULL)
                    );
                ');
            break;
            default:
                $rows = DB::select('
                    SELECT `TT_Sim`.`id`, `idModello`, `serial`, `apn`, `dataAttivazione`, `dataDisattivazione`, `TT_Brand`.`marca`, `TT_Modello`.`modello`
                    FROM `TT_Sim`
                    LEFT OUTER JOIN `TT_Modello` ON `TT_Sim`.`idModello` = `TT_Modello`.`id`
                    LEFT OUTER JOIN `TT_Brand` ON `TT_Modello`.`id` = `TT_Brand`.`id`
                    WHERE 1 AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Componente WHERE idSim IS NOT NULL
                    ) AND `TT_Sim`.`id` NOT IN (
                        SELECT idSim FROM TT_Servizio INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Servizio`.`id` WHERE idSim IS NOT NULL AND (`dataFine` >= now() OR `dataFine` IS NULL)
                    );
                ');
            break;
        }
        return $rows;
    }

    public function create(SimRequest $request, int $id = null)
    {
        $req = $request->validated();
        if( !is_null($id) )
        {
            TT_SimModel::findOrFail($id)->update($req);
            return TT_SimModel::find($id);
        }else{
            return TT_SimModel::create($req);
        }
        return 7;
    }
}
