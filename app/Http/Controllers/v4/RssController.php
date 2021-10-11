<?php
namespace App\Http\Controllers\v4;

use Illuminate\Support\Facades\Redis;

class RssController
{
    public static function call_external($url, $method, $content = [], $header = [])
    {
        if (is_array($header) && count($header) > 1) {
            $header = implode("\r\n", $header);
        }

        if (is_array($content) && count($content) >= 1) {
            $content = http_build_query($content);
        }else{
            $content = false;
        }
        $context = [
            'http' => [
                'method'  => $method,
                'header'  => $header,
                'content' => $content,
                'timeout' => 60
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ],
        ];
        // exit(json_encode($context));
        $context = stream_context_create($context);

        $resp = file_get_contents($url, $content, $context);

        return $resp;
    }

    public static function set_all()
    {
        $url = 'https://www.cciss.it/rss';

        $vals = simplexml_load_string(self::call_external($url, "GET"));

        foreach ($vals->channel->item as $info) {
            $title = nl2br(strip_tags(((array) $info->title)[0]));
            $desc = strip_tags(((array) $info->description)[0]);
            $desc = ucfirst(str_replace($title."\n ", '', $desc));
            $fonte = substr($desc, strpos($desc, "(Fonte: "), strlen($desc));
            $desc = trim(substr($desc, 0, strpos($desc, "(Fonte: ")));
            $myVals[] = [
                "title" => $title,
                "description" => $desc,
                "fonte" => $fonte,
                "pubDate" => strtotime(strip_tags(((array) $info->pubDate)[0]))
            ];
        }

        Redis::set('RSS', json_encode($myVals));

        return true;
    }

    function get_all(){
        return json_decode(Redis::get('RSS'));
    }
}

/**
 * 
 * 
 * <?php
namespace App\Http\Controllers\v4;

use Illuminate\Support\Facades\Redis;

class RssController
{
    public static function call_external($url, $method, $content = [], $header = [])
    {

        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $url,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => $method,
        //     CURLOPT_POSTFIELDS => "",
        //     CURLOPT_HTTPHEADER => [
        //         "Content-Type: application/xml",
        //         "Accept: application/xml"
        //     ],
        // ));
        // $resp = curl_exec($curl);
        // $err  = curl_error($curl);
        // curl_close($curl);
        // dd(curl_version(), $err, $resp);

        // $fermate = 0;
        // dd($fermate);
        if (is_array($header) && count($header) > 1) {
            $header = implode("\r\n", $header);
        }

        if (is_array($content) && count($content) >= 1) {
            $content = http_build_query($content);
        }else{
            $content = false;
        }
        $context = [
            'http' => [
                'method'  => $method,
                'header'  => $header,
                'content' => $content,
                'timeout' => 60
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true
            ],
        ];
        // exit(json_encode($context));
        $context = stream_context_create($context);

        $resp = file_get_contents($url, $content, $context);

        return $resp;
    }

    public static function set_all()
    {
        $url = 'https://www.cciss.it/rss';

        $vals = simplexml_load_string(self::call_external($url, "GET"));

        foreach ($vals->channel->item as $info) {
            $title = nl2br(strip_tags(((array) $info->title)[0]));
            $desc = strip_tags(((array) $info->description)[0]);
            $desc = ucfirst(str_replace($title."\n ", '', $desc));
            $fonte = substr($desc, strpos($desc, "(Fonte: "), strlen($desc));
            $desc = trim(substr($desc, 0, strpos($desc, "(Fonte: ")));
            $myVals[] = [
                "title" => $title,
                "description" => $desc,
                "fonte" => $fonte,
                "pubDate" => strtotime(strip_tags(((array) $info->pubDate)[0]))
            ];
        }

        Redis::set('RSS', json_encode($myVals));

        return true;
    }

    function get_all(){
        return json_decode(Redis::get('RSS'));
    }
}
?>
 */
?>