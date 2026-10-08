<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('account.show', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->paginate(10),
        ]);
    }
}