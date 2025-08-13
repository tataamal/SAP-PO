<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SAPLoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Simpan intended URL sebelum login SAP, hanya jika belum login
        if (!session()->has('sap_user') && !session()->has('sap_pass')) {
            session(['url.intended.sap' => url()->previous()]);
        }

        return view('sap-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'sap_username' => 'required',
            'sap_password' => 'required',
        ]);

       $response = Http::asJson()->post('http://127.0.0.1:8006/api/sap-login', [
            'username' => $request->sap_username,
            'password' => $request->sap_password,
        ]);

        if ($response->successful()) {
            // Simpan ke session
            session(['sap_user' => $request->sap_username]);
            session(['sap_pass' => $request->sap_password]);

            // Redirect ke halaman yang dituju sebelum login SAP
            $redirectTo = session('url.intended.sap', route('dashboard'));
            session()->forget('url.intended.sap');

            return redirect()->to($redirectTo);
        }

        return back()->withErrors([
            'msg' => 'Login ke SAP gagal: ' . $response->json('error', 'Unknown error'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['sap_user', 'sap_pass']);
        return redirect()->route('sap.login');
    }
}
