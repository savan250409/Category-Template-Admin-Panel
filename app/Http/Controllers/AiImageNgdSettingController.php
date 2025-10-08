<?php

namespace App\Http\Controllers;

use App\Models\AiImageNgdSetting;
use Illuminate\Http\Request;

class AiImageNgdSettingController extends Controller
{
    public function index()
    {
        $settings = AiImageNgdSetting::all();
        return view('setting.ngd.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        if (AiImageNgdSetting::count() >= 1) {
            return redirect()->back()->with('error', 'Only 1 setting allowed. Please edit the existing one.');
        }

        AiImageNgdSetting::create($request->only('model'));

        return redirect()->route('ai-image-ngd-setting.index')->with('success', 'Setting created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        $setting = AiImageNgdSetting::findOrFail($id);
        $setting->update($request->only('model'));

        return redirect()->route('ai-image-ngd-setting.index')->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = AiImageNgdSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('ai-image-ngd-setting.index')->with('success', 'Setting deleted successfully.');
    }
}
