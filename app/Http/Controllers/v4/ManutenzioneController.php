<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\v4\{MailController, TraxController};
use App\Http\Requests\{ManutenzioneChiusuraRequest, ManutenzioneRequest};
use App\Models\{TT_AnagraficaModel, TT_AnnotazioneModel, TT_CampoAnagraficaModel, TT_FlottaModel, TT_ManutenzioneModel, TT_ServizioModel, TorinoController};
use App\Repositories\Posizione as RepositoriesPosizione;
use Carbon\Carbon;
use Facades\App\Repositories\{iHelpU, Posizione};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class ManutenzioneController extends Controller
{
    public function import_from_vania(Request $request, int $idAnagrafica)
    {
        $salva = "online";
        if ($salva == "online") {
            $url = "https://api.recorditalia.net/";
            $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxMSIsImp0aSI6ImViMzk3ZmVmNGY5MDgyZGRjNGMxNzA1YThkNDJhYjQzMWIxZWNjMmQxMjNlZmY4ZTNkMTZiZmFlNmUxOGM5ZGQyMGQ3ODcwZmRjMWRkNTYyIiwiaWF0IjoxNjA4MzA2NDc2LCJuYmYiOjE2MDgzMDY0NzYsImV4cCI6MTYzOTg0MjQ3Niwic3ViIjoiNDIiLCJzY29wZXMiOltdfQ.qrI_2cj3aE1-TxDp4CEKDsN_LFP5b_a72PU7aU6weGccQs69JjLHeO2Y3_oI6v59IjRRtQzVBUOdsBMZOHr1qA0nDRJs904-GaHc8apOiuH08rf8YMbWAp717-rGnaKJbZLb2uZEh_Cdx5_n7DeswJ3U__csTLN4Hyt0-ApNEZYUlYfczrE8EOJm_cika_z1eAigu7Nx6bVsbCIvvR_dTWjp2eG6R0JJ_bRpdUl7VZWsOHJBYPzl9Zcyo3tvMJP3lOf8J-OkmW5G0JhJespiFuPJg4RaPKDaWSy60eLSWEGgmzu1rI01W5Pzf1EDFvplaVczuwascypRGEVAamoNW_GsOCIb73b9GWOUjT0Ny4IyJ444ULVfsPpcParkewsXDbKD0TZbalVsvEf3EphB5MKKJxGZNnJyZrB2SeUo_DrQ2kbP2d21V0mY5Zew_NZwY7T-MlVQU6xIJumJYBCsyt7mkFL5vBv1ypZkc7cI6knn6CmVhvLgse4AmDxEO9LwCdU5ZaAPr2T1PNQdUYal2Z8bLdXiXba3tVOpfsMOVxoOuUOCUMv_4fTtg05VC8cUZAFnMrjeY52b5lHp3LIf9AqlpKNXklU7JrlDtSvU2dzJfOckZdNLtysiSPiqMVEvB0JORpehXVmkHAhcjaPoyJnxpd3O34Ax76Yi-n2ylLU";
        } elseif ($salva == "sandbox") {
            $url = "testapi.recorditalia.net/";
            $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiYzI3YzRmMmU0NjFjMTM1MTNhNTBiYjAxMWJjMjlkMjJiMWRlMWVkMjBlNTE1OWZiZTgzZmQzYjVkOTdjYmZlMWFiNmNkZTI2ZDhmZGVmNGUiLCJpYXQiOjE2MDgzMDY4NTYsIm5iZiI6MTYwODMwNjg1NiwiZXhwIjoxNjM5ODQyODU2LCJzdWIiOiI0MiIsInNjb3BlcyI6W119.J3bdF6sf3bqARVwL-sYaB8V_Jb2w_EOcdfFhvtzY3wG6mhOIPbT7nkdnULtSQJdHeAfMG7FFjfhC4NungETLl0_3jwlZHHiIOurCJIaER9WAntyj4dGQoYL_DA_jmSx2Ryg4acmOJd2Pk23E4rncJtp-u3UfspvF3OJXzgEONnxGiXRU8DUiEZiVLFqHvFZYjxTNq1TWDI_ju7f7MpgVZcLHcEk0tNgMGiinC8o03Rx9Fy10EXhEHNt8nVZGxMF1IBQMvZtOLa3o5zlYRv_Q5YR8MDgufyCwpwa1Bi3494e5l3kJYuqeCPtoa_AhYZ19h0xaviFlS3wz4_xYGLIIUOsSXs_eOmz3cTpBhy1wr_LV5KNCFHN6H0b3iJODgJt1rBskw7n_UhbGebb1LuAUOoykBtNRcHxqIoOnAL9e3xJM8YxHdWvxHrqe212pLv0SIMfwEoOgnuB9XXj3q_v5hqnyVg-1zNzmFS7Mu1lcicNCL5mBC6TYi-Cu51Fd0ymN3m66UlTqnYEfVoYv6trtPF5ZEiHFRrW_yFNES56nmacvi4ljurxN5nQ6LTGh5DNXbtLzg-4tJuH0OcRSl6kaeh7zcXEN49PP5NIt3vZsm2VT9q7ryp2E98x-R8If670nTAgK9vlpwt6wGLz-3q_nZ3PDAb-lYcA9lpxQo5JF96I";
        } else {
            $url = "http://api.record.local/";
            $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNTkzMDc2NDJjNzk2NWFmOTVlODQ4OTllODAzYzdiODAzMWI5ZmJjNWFiYTIwYWM4ZmY1NGFjOWI0NmQxM2ZlZmQwMzlhNWVkMDg2NDA0MzEiLCJpYXQiOjE2MDgzMDYzMTUsIm5iZiI6MTYwODMwNjMxNSwiZXhwIjoxNjM5ODQyMzE1LCJzdWIiOiI0MiIsInNjb3BlcyI6W119.YBlE0xs-lF8nKnmMhb7bQgoIsnMTiDvyA_h8RmMPdDJfdwigvEptEWhDQ7PQidlV4iXRjmzQZPOKArNWMgHBlLiZRZ-jLvIVQkVnR3Bt57PUHTnOI5ryGOwbjUzBbKiwII2I9a2EJREDftXWwGPHT1Ni8Py7e9g83yBBHphrCam3E_-FO3lpOn4eVuTw582EhVjQ7VmLJ9fE-Dpr39K96L-lucBCVRn5L1VsgkLo2wc_xsiNWb4aVkcf7peCmswPh9o3skquA03JgrWgNYySGreud1fuZxBpeTBDcIIlHsl72RMxuuLBD3XBLS9S-ptLQT6mTnypE9FlrIVFTY3sMB7_N79QtjzhLDsXmNkzmTmSgl_7UApLc-TOSke5JYknAB_voIzwBoHVicCP6TRPi1Dj7390Jw0tpJuFCav4susxuO5eBQiGYsl76aI2dvy0qTOE76YEs6QzkcmMn9x0l7aglKHtPOtzTHe-FqHUjAUkX9Sz6H37Vp_zW5BvdFOK2VqPZLK19yBrgxI3b4AOAYcWqLamTzqSwks2Z4-WHNh4qLWoxvAZqk9cGCouUoZArpQi-m1cAr55tmNnFtklpsdcnMrmM87SBJDwwcG2mEWC50dDgNZFJqBVi-DmwCwA7UDj7QWSkNoZd_YhL7ysvzQE9OvyTzJjW0hom0mNHCQ";
        }
        try {
            $anagrafica = TT_AnagraficaModel::findOrFail($idAnagrafica);

            $request_import = iHelpU::groupBy($request->all(), 'unitcode');

            $servizi = TT_ServizioModel::where('idAnagrafica', $anagrafica->id)->get();

            $requests_manutenzione = [];

            foreach ($servizi as $servizio) {
                foreach ($servizio->gps as $gps) {
                    if (array_key_exists($gps->unitcode, $request_import)) {
                        foreach ($request_import[$gps->unitcode] as $req) {
                            $tmp = [];
                            $tmp['idServizio'] = $servizio->id;
                            $tmp['idTipologia'] = 105;
                            $tmp['campo_anagrafica_tipologia'] = (object)[
                                'nome' => $req->custom_tipologia
                            ];
                            if ($req->base_km) {
                                $tmp['km_start'] = $req->fine - $req->soglia;
                                $tmp['km_intervallo'] = $req->soglia;
                            } else {
                                $tmp['ore_start'] = (($req->fine - $req->soglia) / (1000 * 60 * 60));
                                $tmp['ore_intervallo'] = ($req->soglia / (1000 * 60 * 60));
                            }

                            $tmp['campo_anagrafica_email'] = (object) [
                                'nome' => 'info@coromano.it'
                            ];
                            $tmp_rq = new ManutenzioneRequest();
                            $tmp_rq->headers->set('content-type', 'application/json');
                            $tmp_rq->initialize((array) $tmp);
                            $requests_manutenzione[] = $tmp_rq;
                        }
                        unset($request_import[$gps->unitcode]);
                    }
                }
            }
            foreach ($requests_manutenzione as $rm) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url . "api/v3/manutenzione",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($rm->all()),
                    CURLOPT_HTTPHEADER => [
                        "Content-Type: application/json",
                        "Accept: application/json",
                        "X-Requested-With: XMLHttpRequest",
                        "Authorization: Bearer " . $token
                    ],
                ));
                curl_exec($curl);
                curl_close($curl);
            }
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getAll(string $status = null)
    {
        switch ($status) {
            case "opened":
                $manutenzioni = TT_ManutenzioneModel::whereNull('data_ritiro');
                break;
            case "closed":
                $manutenzioni =  TT_ManutenzioneModel::whereNotNull('data_ritiro');
                break;
            case "expiring":
                $manutenzioni =  TT_ManutenzioneModel::whereNull('data_ritiro')->where('sent_preavviso', 1);
                break;
            default:
                $manutenzioni = TT_ManutenzioneModel::whereNotNull('id');
                break;
        }

        return $manutenzioni->with('tipologia', 'note', 'custom_tipologia', 'custom_officina', 'custom_email', 'servizio.mezzo')->get();
    }

    public function getId(int $id)
    {
        return TT_ManutenzioneModel::findOrFail($id)
            ->load('tipologia', 'custom_tipologia', 'custom_officina', 'custom_email', 'servizio.mezzo', 'note');
    }

    public function delete(int $id)
    {
        $manutenzione = TT_ManutenzioneModel::findOrFail($id)->load('note');
        foreach ($manutenzione->note as $nota) {
            $nota->delete();
        }

        return $manutenzione->delete();
    }

    public function findFromServizio(int $idServizio, string $params = null)
    {
        $manutenzioni = TT_ServizioModel::find($idServizio)->manutenzioni()->with('tipologia', 'custom_tipologia', 'custom_officina', 'custom_email', 'servizio.mezzo');

        switch ($params) {
            case 'opened':
                $manutenzioni->whereNull('data_ritiro');
                break;
            case 'closed':
                $manutenzioni->whereNotNull('data_ritiro');
                break;
            case 'expiring':
                $manutenzioni->whereNull('data_ritiro')->where('sent_preavviso', 1);
                break;
        }

        return $manutenzioni->get();
    }

    public function findFromFlotta(int $idFlotta, string $params = null)
    {
        $serviziInFlotta = TT_FlottaModel::find($idFlotta)->servizi;
        $servizi = [];
        foreach ($serviziInFlotta as $sif) {
            $servizi[$sif->id] = $sif->pivot->nickname;
        }

        $flotta = TT_ServizioModel::findMany(array_keys($servizi)); //->with('manutenzioni')->with('tipologia', 'campo_anagrafica', 'officina');
        // $flotta = TT_FlottaModel::find($idFlotta);

        switch ($params) {
            case 'opened':
                $flotta->load([
                    'manutenzioni' => function ($query) {
                        $query->whereNull('data_ritiro');
                    },
                    'manutenzioni.custom_tipologia',
                    'manutenzioni.custom_officina',
                    'manutenzioni.custom_email',
                    'manutenzioni.tipologia',
                    'manutenzioni.servizio.mezzo'
                ]);
                break;
            case 'closed':
                $flotta->load([
                    'manutenzioni' => function ($query) {
                        $query->whereNotNull('data_ritiro');
                    },
                    'manutenzioni.custom_tipologia',
                    'manutenzioni.custom_officina',
                    'manutenzioni.custom_email',
                    'manutenzioni.tipologia',
                    'manutenzioni.servizio.mezzo'
                ]);
                break;
            case 'expiring':
                $flotta->load([
                    'manutenzioni' => function ($query) {
                        $query->whereNull('data_ritiro')->where('sent_preavviso', 1);
                    },
                    'manutenzioni.custom_tipologia',
                    'manutenzioni.custom_officina',
                    'manutenzioni.custom_email',
                    'manutenzioni.tipologia',
                    'manutenzioni.servizio.mezzo'
                ]);
                break;
            default:
                $flotta->load([
                    'manutenzioni',
                    'manutenzioni.custom_tipologia',
                    'manutenzioni.custom_officina',
                    'manutenzioni.custom_email',
                    'manutenzioni.tipologia',
                    'manutenzioni.servizio.mezzo'
                ]);
                break;
        }
        $listOne = [];
        foreach ($flotta as $servizio) {
            if (count($servizio->manutenzioni) >= 1) {
                foreach ($servizio->manutenzioni as $man) {
                    $man->servizio->nickname = $servizi[$man->servizio->id];
                    $listOne[] = $man;
                }
            }
        }
        return $listOne;
    }

    public function create(ManutenzioneRequest $request)
    {
        $manutenzione = $request->validated(); //->except('campo_anagrafica_email', 'campo_anagrafica_tipologia', 'servizio', 'nota');
        unset($manutenzione['campo_anagrafica_email'], $manutenzione['campo_anagrafica_tipologia'], $manutenzione['servizio'], $manutenzione['nota']);
        if (isset($request->campo_anagrafica_email)) {
            $campo_anagrafica_email = $request->input('campo_anagrafica_email');
            if (!isset($campo_anagrafica_email['id'])) {
                $campo_anagrafica_email['idAnagrafica'] = Auth::user()->idAnagrafica;
                $campo_anagrafica_email['idTipologia'] = 114;
                $custom_email = TT_CampoAnagraficaModel::firstOrCreate($campo_anagrafica_email, ['idOperatore' => Auth::user()->id]);
            }
        }

        if (isset($request->campo_anagrafica_tipologia)) {
            $campo_anagrafica_tipologia = $request->input('campo_anagrafica_tipologia');
            if (!isset($campo_anagrafica_tipologia['id'])) {
                $campo_anagrafica_tipologia['idAnagrafica'] = Auth::user()->idAnagrafica;
                $campo_anagrafica_tipologia['idTipologia'] = 110;
                $custom_tipologia = TT_CampoAnagraficaModel::firstOrCreate($campo_anagrafica_tipologia, ['idOperatore' => Auth::user()->id]);
            }
        }

        $tot = [];

        if (isset($request->idServizio)) {
            $request->merge(['servizio' => [(object)['id' => $request->idServizio]]]);
        }

        for ($i = 0; $i < count($request->input('servizio')); ++$i) {
            $manutenzione['idServizio'] = $request->input('servizio.' . $i . '.id');
            $manutenzione['idCampoAnagrafica'] = $custom_tipologia->id;
            $manutenzione['idOperatore'] = Auth::user()->id;
            if (isset($custom_email)) {
                $manutenzione['custom_email_id'] = $custom_email->id;
            }

            $manutenzione['ripeti'] = ($manutenzione['idTipologia'] == 105) ? 1 : 0;

            $thisManutenzine = TT_ManutenzioneModel::firstOrCreate($manutenzione);

            $this->sync_mezzo($thisManutenzine);

            $tot[] = $thisManutenzine;
            if (isset($request->nota)) {
                for ($y = 0; $y < count($request->input('nota')); ++$y) {
                    $nota = $request->input('nota.' . $y);
                    $nota['idRiferimento'] = $thisManutenzine->id;
                    $nota['tabella'] = 'TT_Manutenzione';
                    $nota['idOperatore'] = Auth::user()->id;
                    TT_AnnotazioneModel::firstOrCreate($nota);
                }
            }
        }

        return $tot;
    }

    public function confirm(ManutenzioneChiusuraRequest $request, int $id)
    {
        $stored_manutenzione = TT_ManutenzioneModel::findOrFail($id);
        $stored_manutenzione->load('servizio.mezzo');
        $newData = [];
        if (!is_null($stored_manutenzione->km_start)) {
            $newData['km_start'] = $stored_manutenzione->servizio->mezzo[0]->km_totali;
        }
        if (!is_null($stored_manutenzione->ore_start)) {
            $newData['ore_start'] = $stored_manutenzione->servizio->mezzo[0]->ore_totali;
        }
        if (!is_null($stored_manutenzione->giorno_start)) {
            $newData['giorno_start'] = $request['data_ritiro'];
        }
        $newMan = $stored_manutenzione->replicate()->fill($newData);

        $conferma_manutenzione = $request->validated();
        unset($conferma_manutenzione['custom_officina']);

        if (isset($request->custom_officina)) {
            $custom_officina = $request->input('custom_officina');
            if (!isset($custom_officina['id'])) {
                $custom_officina['idAnagrafica'] = Auth::user()->idAnagrafica;
                $custom_officina['idTipologia'] = 109;
                $custom_officina = TT_CampoAnagraficaModel::firstOrCreate($custom_officina, ['idOperatore' => Auth::user()->id]);
            }
        }

        foreach ($conferma_manutenzione as $key => $val) {
            $stored_manutenzione->{$key} = $val;
        }

        if (isset($custom_officina->id)) {
            $stored_manutenzione->idOfficina = $custom_officina->id;
        }

        $stored_manutenzione->save();
        if ($request->method() == 'POST') {
            $newMan->idOperatore    = Auth::user()->id;
            $newMan->sent_preavviso = 0;
            $newMan->sent_scaduta   = 0;
            $newMan->giorno_end     = null;
            $newMan->km_fine        = null;
            $newMan->ore_fine       = null;
            if ($newMan->ripeti == 1)
                $newMan->save();
        }

        return $newMan;
    }

    public function edit(ManutenzioneRequest $request, int $id)
    {
        $stored_manutenzione = TT_ManutenzioneModel::findOrFail($id);

        $manutenzione = $request->validated(); //->except('campo_anagrafica_email', 'campo_anagrafica_tipologia', 'servizio', 'nota');
        unset($manutenzione['campo_anagrafica_email'], $manutenzione['campo_anagrafica_tipologia'], $manutenzione['servizio'], $manutenzione['nota']);

        if (isset($request->campo_anagrafica_email)) {
            $campo_anagrafica_email = $request->input('campo_anagrafica_email');
            if (!isset($campo_anagrafica_email['id'])) {
                $campo_anagrafica_email['idAnagrafica'] = Auth::user()->idAnagrafica;
                $campo_anagrafica_email['idTipologia'] = 114;
                $custom_email = TT_CampoAnagraficaModel::firstOrCreate($campo_anagrafica_email, ['idOperatore' => Auth::user()->id]);
            }
        }
        if (isset($request->campo_anagrafica_tipologia)) {
            $campo_anagrafica_tipologia = $request->input('campo_anagrafica_tipologia');
            if (!isset($campo_anagrafica_tipologia['id'])) {
                $campo_anagrafica_tipologia['idAnagrafica'] = Auth::user()->idAnagrafica;
                $campo_anagrafica_tipologia['idTipologia'] = 110;
                $custom_tipologia = TT_CampoAnagraficaModel::firstOrCreate($campo_anagrafica_tipologia, ['idOperatore' => Auth::user()->id]);
            }
        }

        // Azzero i tre valori d'intervallo, etc e rimetto quelli giusti dopo
        if (!isset($manutenzione['km_start'])) {
            $stored_manutenzione->km_start = null;
            $stored_manutenzione->km_intervallo = null;
            $stored_manutenzione->km_fine = null;
        }
        if (!isset($manutenzione['ore_start'])) {
            $stored_manutenzione->ore_start = null;
            $stored_manutenzione->ore_intervallo = null;
            $stored_manutenzione->ore_fine = null;
        }
        if (!isset($manutenzione['giorno_start'])) {
            $stored_manutenzione->giorno_start = null;
            $stored_manutenzione->giorni_intervallo = null;
            $stored_manutenzione->giorno_end = null;
        }

        // Setto i campi modificati
        foreach ($manutenzione as $key => $val) {
            $stored_manutenzione->{$key} = $val;
        }

        if (isset($custom_email->id)) {
            $stored_manutenzione->custom_email_id = $custom_email->id;
        }

        if (isset($custom_tipologia->id)) {
            $stored_manutenzione->idCampoAnagrafica = $custom_tipologia->id;
        }
        $stored_manutenzione->ripeti = ($manutenzione['idTipologia'] == 105) ? 1 : 0;
        $stored_manutenzione->idOperatore = Auth::user()->id;
        $stored_manutenzione->save();


        $this->sync_mezzo($stored_manutenzione);

        if (isset($request->nota)) {
            for ($y = 0; $y < count($request->input('nota')); ++$y) {
                $nota = $request->input('nota.' . $y);
                $nota['idRiferimento'] = $stored_manutenzione->id;
                $nota['tabella'] = 'TT_Manutenzione';
                $nota['idOperatore'] = Auth::user()->id;
                TT_AnnotazioneModel::firstOrCreate($nota);
            }
        }

        return $stored_manutenzione;
    }

    public function get_all_custom_tipologia(string $params)
    {
        switch ($params) {
            case "email":
                $all_customs = TT_CampoAnagraficaModel::where('idAnagrafica', Auth::user()->idAnagrafica)
                    ->where('idTipologia', 114)
                    ->get();
                break;
            case "tipologia":
                $all_customs = TT_CampoAnagraficaModel::where('idAnagrafica', Auth::user()->idAnagrafica)
                    ->where('idTipologia', 110)
                    ->get();
                break;
            case "officina":
                $all_customs = TT_CampoAnagraficaModel::where('idAnagrafica', Auth::user()->idAnagrafica)
                    ->where('idTipologia', 109)
                    ->get();
                break;
        }
        return $all_customs;
    }

    public function edit_custom_tipologia(Request $request, int $id)
    {
        $custom = TT_CampoAnagraficaModel::findOrFail($id);

        $custom->nome = $request->input('nome');

        $custom->save();

        return $custom;
    }

    public function delete_custom_tipologia(int $id)
    {
        $custom = TT_CampoAnagraficaModel::findOrFail($id);

        $custom->deleted = 1;

        $custom->save();

        return response()->noContent();
    }

    public function update_mezzi()
    {
        // ? Spostato nel comando di update:mezzi
        // $manutenzioni = $this->getAll('opened');
        // $toUpdate = [];

        // foreach ($manutenzioni as $manut) {
        //     try {
        //         if (!isset($manut->servizio) || count($manut->servizio->gps) < 1 || count($manut->servizio->mezzo) < 1) {
        //             continue;
        //         }
        //         $gps = null;
        //         if (count($manut->servizio->gps) >= 1) {
        //             foreach ($manut->servizio->gps as $key => $unGps) {
        //                 if ($key == 0 || $unGps->principale == 1) {
        //                     $gps = $unGps;
        //                 }
        //             }
        //         } else {
        //             $gps = $manut->servizio->gps[0];
        //         }
        //         $pos = RepositoriesPosizione::getLatestsIDs([$gps->unitcode])[0];

        //         $manut->servizio->mezzo[0]->km_totali = $pos->km;

        //         if ($gps->servizioComponente->parziale) {
        //             $req = [
        //                 'idServizio' => [$manut->servizio->id],
        //                 'FromDate' => (new Carbon())->subHours(24)->isoFormat('YYYY-MM-DD HH:mm:ss')
        //             ];
        //             // dump($manut->servizio->id . ' -.-.-.-.-.-.-.-.- ' . $manut->servizio->mezzo[0]->ore_totali);
        //             $time = (new TraxController())->parzialeGlobale($req, $manut->servizio->id);
        //             if (isset($time['TEMPO_ON'])) {
        //                 $manut->servizio->mezzo[0]->ore_totali += ($time['TEMPO_ON']['Valore'] / 3600);
        //                 $manut->servizio->mezzo[0]->ore_totali = round($manut->servizio->mezzo[0]->ore_totali, 3);
        //             }
        //             // dump($manut->servizio->mezzo[0]->ore_totali);
        //         }

        //         $manut->servizio->mezzo[0]->save();
        //         $toUpdate[] = $manut;
        //     } catch (\Throwable $th) {
        //         // throw $th;
        //     }
        // }
        try {
            $this->get_preavviso();
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            $this->get_allarmi();
        } catch (\Throwable $th) {
            //throw $th;
        }
        // TODO quando passi in prod ricorda di inviare mail fine!
        // $this->sync_man_sandbox_prod();
        return true;
    }

    public function get_preavviso() // Todo: Nelle mail mancano le note..
    {
        $manutenzioni = $this->getAll('opened')->where('sent_preavviso', 0)->where('sent_scaduta', 0);

        $default_km_preavviso     = 5000;
        $default_ore_preavviso    = 40;
        $default_giorni_preavviso = 7;

        foreach ($manutenzioni as $man) {
            try {
                if (!isset($man->servizio->mezzo[0])) continue;

                $km_preavviso     = (!is_null($man->km_preavviso))      ? $man->km_preavviso        : $default_km_preavviso;
                $ore_preavviso    = (!is_null($man->ore_preavviso))     ? $man->ore_preavviso       : $default_ore_preavviso;
                $giorni_preavviso = (!is_null($man->giorni_preavviso))  ? $man->giorni_preavviso    : $default_giorni_preavviso;

                // ? Calcolo valore km_fine -> se non calcolato e se richiesto
                if (!(is_null($man->km_intervallo) && is_null($man->km_start)) && is_null($man->km_fine)) {
                    $man->km_fine = $man->km_intervallo + $man->km_start;
                }
                // ? Calcolo valore ore_fine -> se non calcolato e se richiesto
                if (!(is_null($man->ore_intervallo) && is_null($man->ore_start)) && is_null($man->ore_fine)) {
                    $man->ore_fine = $man->ore_intervallo + $man->ore_start;
                }
                // ? Calcolo valore giorno_end -> se non calcolato e se richiesto
                if (!(is_null($man->giorni_intervallo) && is_null($man->giorno_start)) && is_null($man->giorno_end)) {
                    $man->giorno_end = (new Carbon($man->giorno_start))->addDays($man->giorni_intervallo)->isoFormat('YYYY-MM-DD');
                    // ->subDays($giorni_preavviso);
                }


                $preavviso = (object) [];
                $preavviso->ok = false;
                $preavviso->ore = false;
                $preavviso->giorno = false;
                if (!is_null($man->km_fine) && $man->servizio->mezzo[0]->km_totali >= ($man->km_fine - $km_preavviso)) {
                    $preavviso->ok = true;
                }
                if (!is_null($man->ore_fine) && $man->servizio->mezzo[0]->ore_totali >= ($man->ore_fine - $ore_preavviso)) {
                    $preavviso->ore = true;
                }
                if (!is_null($man->giorno_end) && now() >= (new Carbon($man->giorno_end))->subDays($giorni_preavviso)) {
                    $preavviso->giorno = true;
                }
                $man->preavviso = $preavviso;
                if (isset($man->custom_email->nome) && (new MailController)->sendMailPreavvisoManutenzione($man)) {
                    $man->sent_preavviso = 1;
                }
                unset($man->preavviso);
                $man->save();
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        return true;
        // Todo: Controlla se i km, ore e data sono state superate e invia mail.
    }

    public function get_allarmi()
    {
        $manutenzioni = $this->getAll('opened')->where('sent_scaduta', 0);

        foreach ($manutenzioni as $man) {
            try {
                if (!isset($man->servizio->mezzo[0])) continue;
                // ? Calcolo valore km_fine -> se non calcolato e se richiesto
                if (!(is_null($man->km_intervallo) && is_null($man->km_start)) && is_null($man->km_fine)) {
                    $man->km_fine = $man->km_intervallo + $man->km_start;
                }

                // ? Calcolo valore ore_fine -> se non calcolato e se richiesto
                if (!(is_null($man->ore_intervallo) && is_null($man->ore_start)) && is_null($man->ore_fine)) {
                    $man->ore_fine = ($man->ore_intervallo + $man->ore_start);
                }

                // ? Calcolo valore giorno_end -> se non calcolato e se richiesto
                if (!(is_null($man->giorni_intervallo) && is_null($man->giorno_start)) && is_null($man->giorno_end)) {
                    $man->giorno_end = (new Carbon($man->giorno_start))->addDays($man->giorni_intervallo)->isoFormat('YYYY-MM-DD');
                }


                $scadenze = (object) [];
                if (!is_null($man->km_fine) && $man->servizio->mezzo[0]->km_totali >= $man->km_fine) {
                    $scadenze->km = true;
                }else{
                    $scadenze->km = false;
                }

                if (!is_null($man->ore_fine) && $man->servizio->mezzo[0]->ore_totali >= $man->ore_fine) {
                    $scadenze->ore = true;
                }else{
                    $scadenze->ore = false;
                }

                if (!is_null($man->giorno_end) && now() >= (new Carbon($man->giorno_end))) {
                    $scadenze->giorno = true;
                }else{
                    $scadenze->giorno = false;
                }

                $man->scadenze = $scadenze;

                if (isset($man->custom_email->nome) && (new MailController)->sendMailScadutaManutenzione($man)) {
                    $man->sent_scaduta = 1;
                }
                unset($man->scadenze);
                $man->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        return true;
    }

    public function sync_mezzo(TT_ManutenzioneModel $manutenzione)
    {
        $servizio = TT_ServizioModel::find($manutenzione->idServizio);
        if (isset($servizio->mezzo) && count($servizio->mezzo) == 1) {
            $mezzo = $servizio->mezzo[0];
            if (!empty($manutenzione->ore_start) && (is_null($mezzo->ore_totali) || $mezzo->ore_totali < $manutenzione->ore_start)) {
                $mezzo->ore_totali = $manutenzione->ore_start;
            }

            if (!empty($manutenzione->km_start) && (is_null($mezzo->km_totali) || $mezzo->km_totali < $manutenzione->km_start)) {
                $mezzo->km_totali = $manutenzione->km_start;

                // $new_req = $this->mkRequest(['km' => $manutenzione->km_start]);
                // (new TorinoController)->vehicle($new_req, $manutenzione->idServizio);
            }
            $mezzo->save();
        }
        return true;
    }

    public function send_test_mail()
    {
        $this->get_preavviso();
        $this->get_allarmi();
        $this->sync_man_sandbox_prod();
    }

    public function test_pre_mail()
    {
        $manutenzioni = $this->getAll('opened')
            ->where('sent_scaduta', 0);

        $outing_mail = [
            'success' => [],
            'error'   => [],
        ];

        foreach ($manutenzioni as $man) {
            try {
                if (!isset($man->servizio->mezzo[0])) continue;

                if (!(is_null($man->km_intervallo) && is_null($man->km_start)) && is_null($man->km_fine)) {
                    $man->km_fine = $man->km_intervallo + $man->km_start;
                }

                if (!(is_null($man->ore_intervallo) && is_null($man->ore_start)) && is_null($man->ore_fine)) {
                    $man->ore_fine = ($man->ore_intervallo + $man->ore_start);
                }

                if (!(is_null($man->giorni_intervallo) && is_null($man->giorno_start)) && is_null($man->giorno_end)) {
                    $man->giorno_end = (new Carbon($man->giorno_start))->addDays($man->giorni_intervallo)->isoFormat('YYYY-MM-DD');
                }
                $scadenze = (object) [];
                if (!is_null($man->km_fine) && $man->servizio->mezzo[0]->km_totali >= $man->km_fine) {
                    $scadenze->km_fine = true;
                }

                if (!is_null($man->ore_intervallo) && $man->servizio->mezzo[0]->ore_totali >= $man->ore_start) {
                    $scadenze->ore_intervallo = true;
                }

                if (!is_null($man->giorno_end) && now() >= $man->giorno_end) {
                    $scadenze->giorno_end = true;
                }

                $man->scadenze = $scadenze;
                if (isset($man->custom_email->nome)) {
                    $outing_mail['success'][] = $man;
                }
            } catch (\Throwable $th) {
                $outing_mail['error'][] = $man;
            }
        }

        MailController::job_info((object)[
            'object' => 'Mail test!',
            'body'  => json_encode($outing_mail, JSON_PRETTY_PRINT),
            'nota'   => 'ManutenzioneController::class, test_pre_mail'
        ]);

        return $outing_mail;
    }

    public function sync_man_sandbox_prod()
    {
        $queryes = [];
        $results = DB::select("SELECT `TT_Manutenzione`.`id`, `TT_Manutenzione`.`idServizio`, `TT_Manutenzione`.`sent_preavviso`, `TT_Manutenzione`.`sent_scaduta`, `TT_Mezzo`.`created_at`,`TT_Mezzo`.`updated_at`
        FROM `TT_Manutenzione`
        INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Manutenzione`.`idServizio`
        INNER JOIN `TT_Mezzo` ON `TT_Mezzo`.`id` = `TC_ServizioComponente`.`idMezzo`
        WHERE `idMezzo` IS NOT NULL
        AND sent_preavviso = 1;");

        $id_preavvisi = array_keys(iHelpU::groupBy($results, 'id'));
        $queryes[] = "UPDATE `TT_Manutenzione` SET sent_preavviso = 1 WHERE id IN (" . implode(',', $id_preavvisi) . ")";

        $results = DB::select("SELECT `TT_Manutenzione`.`id`, `TT_Manutenzione`.`idServizio`, `TT_Manutenzione`.`sent_preavviso`, `TT_Manutenzione`.`sent_scaduta`, `TT_Mezzo`.`created_at`,`TT_Mezzo`.`updated_at`
        FROM `TT_Manutenzione`
        INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idServizio` = `TT_Manutenzione`.`idServizio`
        INNER JOIN `TT_Mezzo` ON `TT_Mezzo`.`id` = `TC_ServizioComponente`.`idMezzo`
        WHERE `idMezzo` IS NOT NULL
        AND sent_scaduta = 1;");

        $id_scadute = array_keys(iHelpU::groupBy($results, 'id'));
        $queryes[] = "UPDATE `TT_Manutenzione` SET sent_scaduta = 1 WHERE id IN (" . implode(',', $id_scadute) . ")";

        $body = '';
        foreach ($queryes as $query) {
            if ($body != '') $body .= "<br>";
            $body .= $query;
        }
        $mail = (object) [
            'object' => "Job Manutenzioni completato",
            'body'   => $body,
            'nota'   => "Ricorda di eseguire questa query sul db di prod"
        ];
        MailController::job_info($mail);

        return $queryes;
    }
}
