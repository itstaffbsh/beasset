<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\ImageOptimizerService;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load(['office', 'department']));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users,email," . $request->user()->id,
            "phone" => "nullable|string|max:255",
            "address" => "nullable|string",
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->has('phone')) {
            $user->phone = $request->phone;
            $user->phone_number = $request->phone;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        $user->save();

        return response()->json($user->load(['office', 'department']));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            "current_password" => "required|current_password",
            "password" => "required|string|min:8|confirmed",
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(["message" => "Password updated"]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            "photo" => "nullable|image|max:10240",
            "avatar" => "nullable|image|max:10240",
        ]);

        $user = $request->user();
        $file = $request->file("photo") ?? $request->file("avatar");
        
        if ($file) {
            if ($user->avatar && Storage::disk("public")->exists($user->avatar)) {
                Storage::disk("public")->delete($user->avatar);
            }
            
            // Kompresi otomatis dan konversi gambar ke format WebP
            $path = ImageOptimizerService::convertToWebp($file, "users", 80, 1200);
            $user->avatar = $path;
            $user->save();
        }

        return response()->json($user->load(['office', 'department']));
    }
}

