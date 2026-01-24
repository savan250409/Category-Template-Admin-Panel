<?php

namespace App\Http\Controllers;

use App\Models\AiBabyVideoModuleSetting;
use Illuminate\Http\Request;

class AiBabyVideoModuleSettingController extends Controller
{

    public function index()
    {
        $settings = AiBabyVideoModuleSetting::all();
        return view('setting.babyvideo.index', compact('settings'));
    }

    public function create()
    {
        return view('setting.babyvideo.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        AiBabyVideoModuleSetting::create($request->only('model'));

        return redirect()->route('ai-baby-video-module-setting.index')
            ->with('success', 'Setting created successfully.');
    }

    public function edit($id)
    {
        $setting = AiBabyVideoModuleSetting::findOrFail($id);
        return view('setting.babyvideo.index', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        $setting = AiBabyVideoModuleSetting::findOrFail($id);
        $setting->update($request->only('model'));

        return redirect()->route('ai-baby-video-module-setting.index')
            ->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = AiBabyVideoModuleSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('ai-baby-video-module-setting.index')
            ->with('success', 'Setting deleted successfully.');
    }
}
