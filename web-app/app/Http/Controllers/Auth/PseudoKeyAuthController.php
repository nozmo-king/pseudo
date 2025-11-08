<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Elliptic\EC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PseudoKeyAuthController extends Controller
{
    private EC $ec;

    public function __construct()
    {
        $this->ec = new EC('secp256k1');
    }

    public function challenge(Request $request)
    {
        $nonce = Str::random(64);
        $domain = config('app.url');

        $message = "Sign this message to authenticate with Pseudochan\n\nDomain: {$domain}\nNonce: {$nonce}";

        session(['auth_nonce' => $nonce]);

        return response()->json([
            'nonce' => $nonce,
            'domain' => $domain,
            'message' => $message,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pubkey' => 'required|string',
            'signature' => 'required|string',
        ]);

        $nonce = session('auth_nonce');
        if (!$nonce) {
            return response()->json(['error' => 'No challenge found'], 400);
        }

        $domain = config('app.url');
        $message = "Sign this message to authenticate with Pseudochan\n\nDomain: {$domain}\nNonce: {$nonce}";
        $messageHash = hash('sha256', $message);

        try {
            $pubkey = $request->input('pubkey');
            $signature = $request->input('signature');

            $key = $this->ec->keyFromPublic($pubkey, 'hex');
            $isValid = $key->verify($messageHash, $signature);

            if ($isValid) {
                $user = User::firstOrCreate(
                    ['pubkey' => $pubkey],
                    ['display_name' => null, 'avatar_path' => null]
                );

                Auth::login($user);
                session()->forget('auth_nonce');

                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'pubkey' => $user->pubkey,
                        'display_name' => $user->display_name,
                        'bitcoin_address' => $this->pubkeyToBitcoinAddress($pubkey),
                    ],
                ]);
            }

            return response()->json(['error' => 'Invalid signature'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Verification failed: ' . $e->getMessage()], 500);
        }
    }

    private function pubkeyToBitcoinAddress(string $pubkey): string
    {
        $pubkeyBin = hex2bin($pubkey);
        $sha256 = hash('sha256', $pubkeyBin, true);
        $ripemd160 = hash('ripemd160', $sha256, true);

        $versioned = "\x00" . $ripemd160;
        $checksum = substr(hash('sha256', hash('sha256', $versioned, true), true), 0, 4);

        $address = $versioned . $checksum;

        return $this->base58Encode($address);
    }

    private function base58Encode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $num = gmp_init(bin2hex($data), 16);
        $encoded = '';

        while (gmp_cmp($num, 0) > 0) {
            list($num, $remainder) = gmp_div_qr($num, 58);
            $encoded = $alphabet[gmp_intval($remainder)] . $encoded;
        }

        for ($i = 0; $i < strlen($data) && $data[$i] === "\x00"; $i++) {
            $encoded = '1' . $encoded;
        }

        return $encoded;
    }
}
