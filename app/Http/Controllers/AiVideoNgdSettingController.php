<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AiVideoNgdSetting;

class AiVideoNgdSettingController extends Controller
{
    public function index()
    {
        $settings = AiVideoNgdSetting::all();
        return view('setting.video_ngd.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        if (AiVideoNgdSetting::count() >= 1) {
            return redirect()->back()->with('error', 'Only 1 setting allowed. Please edit the existing one.');
        }

        AiVideoNgdSetting::create($request->only('model'));

        return redirect()->route('ai-video-ngd-setting.index')->with('success', 'Setting created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        $setting = AiVideoNgdSetting::findOrFail($id);
        $setting->update($request->only('model'));

        return redirect()->route('ai-video-ngd-setting.index')->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = AiVideoNgdSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('ai-video-ngd-setting.index')->with('success', 'Setting deleted successfully.');
    }
}
