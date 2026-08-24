<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    private $apiKey;
    protected array $candidateModels = [
        'gemini-flash-lite-latest',
        'gemini-3.7-flash',
        'gemini-flash-latest',
        'gemini-3.5-flash-lite',
    ];

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', 'AIzaSyBzQW0LjbJiPw7-y_sGauznV91LXG2-g3Y');

        // Log untuk debugging
        Log::info('Gemini Service Initialized', ['api_key_exists' => !empty($this->apiKey)]);
    }

    /**
     * Menghasilkan respon khusus JSON array/object yang valid dan terstruktur
     * dengan otomatis berpindah ke model cadangan jika model utama sibuk (503/429).
     */
    public function generateJson($prompt)
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key is empty');
            throw new \Exception('API Key Google Gemini belum dikonfigurasi.');
        }

        Log::info('Sending JSON request to Gemini API', ['prompt' => $prompt]);
        $lastException = null;

        foreach ($this->candidateModels as $index => $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";
            
            try {
                Log::info("[Gemini AI] Mencoba model #{$index}: {$model}");

                $response = Http::timeout(20)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.2,
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    Log::info("[Gemini AI] Sukses dengan model {$model}", ['raw' => $rawText]);

                    $decoded = json_decode($rawText, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }

                    // Fallback regex jika JSON dibungkus markdown
                    $clean = preg_replace('/```json|```/', '', $rawText);
                    $decoded = json_decode(trim($clean), true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }

                $statusCode = $response->status();
                $errorBody = $response->body();
                Log::warning("[Gemini AI] Model {$model} mengembalikan status {$statusCode}. Mencoba model cadangan...", [
                    'status' => $statusCode,
                    'error' => substr($errorBody, 0, 200)
                ]);

                $lastException = new \Exception($this->getErrorMessage($statusCode, $errorBody));
            } catch (\Exception $e) {
                Log::warning("[Gemini AI] Exception pada model {$model}: {$e->getMessage()}. Mencoba model cadangan...");
                $lastException = $e;
            }
        }

        throw $lastException ?? new \Exception('Seluruh server model Gemini sedang sibuk. Silakan coba sesaat lagi.');
    }

    public function generateResponse($prompt, $context = '')
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key is empty');
            return 'Maaf, konfigurasi AI belum lengkap. Silakan hubungi administrator.';
        }

        $fullPrompt = $this->buildPrompt($prompt, $context);
        Log::info('Sending request to Gemini API', [
            'prompt_length' => strlen($prompt),
            'context_length' => strlen($context)
        ]);

        $lastError = 'Maaf, server AI sedang mengalami masalah.';

        foreach ($this->candidateModels as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::timeout(30)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $result = $this->extractResponse($data);
                    Log::info("[Gemini AI] Chat sukses dengan model {$model}", ['response_length' => strlen($result)]);
                    return $result;
                }

                $errorBody = $response->body();
                $statusCode = $response->status();
                $lastError = $this->getErrorMessage($statusCode, $errorBody);
            } catch (\Exception $e) {
                Log::warning("[Gemini AI Chat] Exception pada {$model}: {$e->getMessage()}");
            }
        }

        return $lastError;
    }

    private function buildPrompt($prompt, $context)
    {
        $shopName = \App\Models\Setting::first()?->shop_name ?? 'POS Cafe & Eatery';
        $systemPrompt = "Anda adalah Asisten AI Cerdas untuk {$shopName}. Anda adalah konsultan dan analis bisnis cafe & resto terpercaya yang ahli dalam analisis penjualan kopi, minuman kekinian, makanan ringan/berat, manajemen HPP & margin, serta rekapitulasi shift kasir.\n\n";

        $systemPrompt .= "KEAHLIAN ANDA:\n";
        $systemPrompt .= "- Menganalisis omset penjualan, jumlah cup/porsi terjual, dan rata-rata per transaksi (AOV)\n";
        $systemPrompt .= "- Memberikan wawasan performa menu (kopi terlaris, pastry, makanan) dan rekomendasi promo\n";
        $systemPrompt .= "- Menjelaskan rekapitulasi shift kasir, rekonsiliasi kas laci, dan analisis selisih kas fisik\n";
        $systemPrompt .= "- Menganalisis profit bersih dan margin keuntungan per menu/kategori\n";
        $systemPrompt .= "- Memberikan ringkasan metode pembayaran (Tunai vs QRIS vs Transfer) dan mode pesanan (Makan di Tempat/Meja vs Bawa Pulang vs Delivery)\n\n";

        $systemPrompt .= "⚠️ PENTING TENTANG TANGGAL:\n";
        $systemPrompt .= "- Jika user menyebut tanggal spesifik (contoh: '18 November 2025', 'kemarin', 'hari ini'), konteks data sudah disiapkan untuk periode tersebut\n";
        $systemPrompt .= "- Lihat header konteks yang menyebutkan tanggal spesifik\n";
        $systemPrompt .= "- GUNAKAN data dari konteks tersebut, JANGAN katakan 'tidak tersedia data untuk tanggal X'\n";
        $systemPrompt .= "- Jika konteks menunjukkan 'TIDAK ADA TRANSAKSI', sampaikan dengan ramah bahwa belum ada transaksi pada tanggal tersebut\n\n";

        $systemPrompt .= "⚠️ PENTING TENTANG DATA PROFIT & MARGIN:\n";
        $systemPrompt .= "- Database sudah memiliki perhitungan profit yang FINAL dan AKURAT\n";
        $systemPrompt .= "- JANGAN PERNAH menghitung ulang profit dengan rumus yang berbeda dari data yang diberikan\n";
        $systemPrompt .= "- Jika konteks data berisi informasi profit, GUNAKAN DATA ITU LANGSUNG\n\n";

        if ($context) {
            $systemPrompt .= "KONTEKS DATA TERKINI DARI SISTEM CAFE:\n{$context}\n\n";
        }

        $systemPrompt .= "GAYA JAWABAN & INSTRUKSI:\n";
        $systemPrompt .= "- Berikan jawaban yang terstruktur, ramah, profesional ala konsultan bisnis cafe yang bersahabat (bisa gunakan emoji seperti ☕, 📊, 💡, 💰, 🕒, 🍽️ jika relevan)\n";
        $systemPrompt .= "- Sajikan data dalam poin-poin rapi atau bullet list yang mudah dibaca cepat di layar kasir/owner\n";
        $systemPrompt .= "- Format semua nominal mata uang dalam format Rupiah standar (contoh: Rp 25.000, Rp 1.500.000)\n";
        $systemPrompt .= "- Berikan saran/actionable tips praktis untuk meningkatkan omset cafe jika diminta rekomendasi bisnis\n\n";

        $systemPrompt .= "Pertanyaan User: {$prompt}\n\nJawaban Lengkap:";

        return $systemPrompt;
    }

    private function extractResponse($data)
    {
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }

        if (isset($data['error']['message'])) {
            Log::error('Gemini API Error in response', ['error' => $data['error']]);
            return 'Maaf, API mengembalikan error: ' . $data['error']['message'];
        }

        return 'Maaf, tidak dapat memproses respons dari AI.';
    }

    private function getErrorMessage($statusCode, $errorBody)
    {
        $errorData = json_decode($errorBody, true);

        if (isset($errorData['error']['message'])) {
            $errorMessage = $errorData['error']['message'];

            if (str_contains($errorMessage, 'API key')) {
                return 'Maaf, API key tidak valid. Silakan periksa konfigurasi.';
            } elseif (str_contains($errorMessage, 'quota')) {
                return 'Maaf, kuota API telah habis. Silakan coba lagi besok.';
            }

            return 'Maaf, error dari AI: ' . $errorMessage;
        }

        switch ($statusCode) {
            case 400:
                return 'Maaf, permintaan tidak valid.';
            case 401:
                return 'Maaf, autentikasi API gagal.';
            case 403:
                return 'Maaf, akses ke API ditolak.';
            case 429:
                return 'Maaf, terlalu banyak permintaan. Silakan coba lagi nanti.';
            case 500:
                return 'Maaf, server AI sedang mengalami masalah.';
            default:
                return 'Maaf, terjadi kesalahan (HTTP ' . $statusCode . '). Silakan coba lagi.';
        }
    }
}
