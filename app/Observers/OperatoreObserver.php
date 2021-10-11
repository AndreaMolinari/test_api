<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OperatoreObserver {
    public function saving(Model $model) {
        if (!($model->idOperatore ?? false))
            $model->idOperatore = Auth::id();
    }
}
