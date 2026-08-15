<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', 'AIzaSyBzQW0LjbJiPw7-y_sGauznV91LXG2-g3Y');

        // Log untuk debugging
        Log::info('Gemini Service Initialized', ['api_key_exists' => !empty($this->apiKey)]);
    }

    public function generateResponse($prompt, $context = '')
    {
        try {
            // Validasi API key
            if (empty($this->apiKey)) {
                Log::error('Gemini API Key is empty');
                return 'Maaf, konfigurasi AI belum lengkap. Silakan hubungi administrator.';
            }

            $fullPrompt = $this->buildPrompt($prompt, $context);

            Log::info('Sending request to Gemini API', [
                'prompt_length' => strlen($prompt),
                'context_length' => strlen($context)
            ]);

            $response = Http::timeout(60) // Increase timeout to 60 seconds
                ->retry(3, 1000) // Retry 3 times with 1 second delay
                ->post($this->baseUrl . '?key=' . $this->apiKey, [
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

            Log::info('Gemini API Response Status', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $this->extractResponse($data);
                Log::info('Gemini API Success', ['response_length' => strlen($result)]);
                return $result;
            }

            // Log detailed error information
            $errorBody = $response->body();
            $statusCode = $response->status();

            Log::error('Gemini API Error', [
                'status_code' => $statusCode,
                'response_body' => $errorBody,
                'api_key' => substr($this->apiKey, 0, 10) . '...' // Log partial key for debugging
            ]);

            return $this->getErrorMessage($statusCode, $errorBody);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API Connection Exception', [
                'message' => $e->getMessage(),
                'api_url' => $this->baseUrl
            ]);
            return 'Maaf, tidak dapat terhubung ke layanan AI. Periksa koneksi internet Anda.';
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Maaf, terjadi kesalahan internal. Silakan coba lagi nanti.';
        }
    }

    private function buildPrompt($prompt, $context)
    {
        $systemPrompt = "Anda adalah asisten AI profesional untuk sistem POS Cafe Noli (Noli Coffee & Eatery). Anda ahli dalam analisis bisnis cafe, penjualan menu (kopi, pastry, food), dan manajemen inventori.\n\n";

        $systemPrompt .= "KEMAMPUAN ANDA:\n";
        $systemPrompt .= "- Menganalisis data penjualan dan transaksi\n";
        $systemPrompt .= "- Memberikan insight bisnis dan rekomendasi\n";
        $systemPrompt .= "- Menjelaskan detail produk, stok, profit, dan kategori\n";
        $systemPrompt .= "- Memberikan ringkasan laporan keuangan\n";
        $systemPrompt .= "- Menjawab pertanyaan tentang performa penjualan\n\n";

        $systemPrompt .= "⚠️ PENTING TENTANG TANGGAL:\n";
        $systemPrompt .= "- Jika user menyebut tanggal spesifik (contoh: '18 November 2025'), konteks data sudah disiapkan untuk tanggal tersebut\n";
        $systemPrompt .= "- Lihat header konteks yang menyebutkan tanggal spesifik (contoh: 'DATA UNTUK TANGGAL 18 November 2025')\n";
        $systemPrompt .= "- GUNAKAN data dari konteks tersebut, JANGAN katakan 'tidak tersedia data untuk tanggal X'\n";
        $systemPrompt .= "- Jika konteks menunjukkan 'TIDAK ADA TRANSAKSI', sampaikan dengan jelas bahwa tidak ada aktivitas pada tanggal tersebut\n\n";

        // 🆕 TAMBAHKAN INSTRUKSI KHUSUS PROFIT
        $systemPrompt .= "⚠️ PENTING TENTANG DATA PROFIT:\n";
        $systemPrompt .= "- Database sudah memiliki kolom 'profit' di tabel transaction_details\n";
        $systemPrompt .= "- Kolom profit berisi perhitungan profit yang SUDAH FINAL dan AKURAT\n";
        $systemPrompt .= "- JANGAN PERNAH menghitung ulang profit dengan rumus apapun\n";
        $systemPrompt .= "- JANGAN mengatakan 'saya tidak memiliki data profit' atau 'tidak dapat memberikan angka profit'\n";
        $systemPrompt .= "- Jika konteks data berisi informasi profit (misalnya 'PROFIT hari ini: Rp X'), GUNAKAN DATA ITU LANGSUNG\n";
        $systemPrompt .= "- Jawab pertanyaan profit dengan data yang tersedia di konteks\n\n";

        if ($context) {
            $systemPrompt .= "KONTEKS DATA TERKINI:\n{$context}\n\n";
        }

        $systemPrompt .= "INSTRUKSI PENTING:\n";
        $systemPrompt .= "- Berikan jawaban yang detail, jelas, dan informatif\n";
        $systemPrompt .= "- Gunakan data yang tersedia untuk memberikan analisis mendalam\n";
        $systemPrompt .= "- Jika diminta detail transaksi atau profit, jelaskan semua informasi yang tersedia di konteks\n";
        $systemPrompt .= "- Jika konteks berisi data profit, LANGSUNG gunakan data tersebut tanpa perhitungan ulang\n";
        $systemPrompt .= "- Berikan rekomendasi bisnis jika relevan\n";
        $systemPrompt .= "- Gunakan bahasa Indonesia yang profesional namun mudah dipahami\n";
        $systemPrompt .= "- Format angka dengan pemisah ribuan (contoh: Rp 1.000.000)\n";
        $systemPrompt .= "- Jika pertanyaan tentang profit, cari kata kunci seperti 'PROFIT hari ini', 'PROFIT bulan ini' di konteks\n\n";

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
