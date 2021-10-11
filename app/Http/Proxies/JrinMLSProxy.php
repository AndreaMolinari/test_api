<?php

namespace App\Http\Proxies;

class JrinMLSProxy extends JrinRecordProxy {

    const BASE_URL        = 'https://centrale.mechanicallinesolutions.net/webtrax/api/v2/';
    const SECRET          = 'jdfkwe32ju34324nbjkbhjkbh888';
    const DEFAULT_HEADERS = [
        'Content-Type: application/x-www-form-urlencoded',
        "Key: Pod854s6"
    ];
    const CACHE_KEY_TOKEN = 'jRin:MLS:token';
    const CACHE_KEY_USER  = 'jRin:MLS:user';
}