<?php

namespace App\Http\Controllers;

use App\Models\AiImageBabyPhotoSetting;
use Illuminate\Http\Request;

class AiImageBabyPhotoSettingController extends Controller
{

    public function index()
    {
        $settings = AiImageBabyPhotoSetting::all();
        return view('setting.babyai.index', compact('settings'));
    }

    public function create()
    {
        return view('setting.babyai.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        AiImageBabyPhotoSetting::create($request->only('model'));

        return redirect()->route('ai-image-baby-photo-setting.index')
                         ->with('success', 'Setting created successfully.');
    }

    public function edit($id)
    {
        $setting = AiImageBabyPhotoSetting::findOrFail($id);
        return view('setting.babyai.index', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model' => 'required|string|max:255',
        ]);

        $setting = AiImageBabyPhotoSetting::findOrFail($id);
        $setting->update($request->only('model'));

        return redirect()->route('ai-image-baby-photo-setting.index')
                         ->with('success', 'Setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = AiImageBabyPhotoSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('ai-image-baby-photo-setting.index')
                         ->with('success', 'Setting deleted successfully.');
    }
}
