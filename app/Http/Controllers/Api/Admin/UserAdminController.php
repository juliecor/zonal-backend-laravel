<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $rows = User::query()
            ->select(['id','name','first_name','middle_name','last_name','phone','email','role','token_balance','created_at'])
            ->orderByDesc('created_at')
            ->paginate(min(max((int)$request->integer('per_page', 20),1),100));
        return response()->json($rows);
    }

    public function addTokens(Request $request, User $user)
    {
        $data = $request->validate([
            'add' => ['required','integer','min:1','max:100000'],
        ]);
        $user->increment('token_balance', $data['add']);
        return response()->json(['ok'=>true, 'user'=>$user->fresh()]);
    }
}
