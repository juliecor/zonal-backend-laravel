<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AppStatusController extends Controller
{
    /** Public: the app checks this on launch to know if maintenance mode is on. */
    public function show()
    {
        return response()->json([
            'maintenance' => Setting::get('maintenance', '0') === '1',
            'message' => Setting::get('maintenance_message', '') ?: '',
        ]);
    }

    /** Admin: turn maintenance mode on/off (with an optional message shown to users). */
    public function setMaintenance(Request $request)
    {
        $data = $request->validate([
            'maintenance' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::put('maintenance', $data['maintenance'] ? '1' : '0');
        if (array_key_exists('message', $data)) {
            Setting::put('maintenance_message', $data['message'] ?? '');
        }

        return response()->json([
            'ok' => true,
            'maintenance' => (bool) $data['maintenance'],
            'message' => $data['message'] ?? '',
        ]);
    }
}
