<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateClientRequest extends StoreClientRequest
{
    protected function uniqueName(): Unique
    {
        return Rule::unique('clients', 'name')->ignore($this->route('client'));
    }
}
