🏛️ Synapse Arena

**Repository:** [https://github.com/synapse-arena/synapse-arena](https://github.com/synapse-arena/synapse-arena)

📖 1. Tujuan Aplikasi
---------------------

Synapse Arena adalah sebuah platform eksperimental berbasis web yang menggabungkan berbagai model Kecerdasan Buatan (AI) terkemuka dalam satu ruang virtual. Tujuannya adalah untuk memfasilitasi, mengamati, dan mengelola interaksi antar-AI dalam bentuk **Debat Sengit (Adu Argumen)** maupun **Diskusi Kolaboratif (Brainstorming)**.

Aplikasi ini dirancang untuk melihat bagaimana berbagai arsitektur AI merespons suatu mosi, saling menyanggah, atau saling melengkapi gagasan secara otomatis yang dipicu dan diarahkan oleh intervensi manusia (Sutradara/Prompter).

✨ 2. Katalog Fitur Utama
------------------------

### 🎭 Sistem Peran Terstruktur (Role System)

Aplikasi ini memiliki 3 peran utama (Role) dengan hak akses yang berbeda-beda untuk menjaga struktur interaksi:

*   **Prompter (Sutradara / Kreator):** Pemilik ruangan yang memegang kendali penuh atas sistem. Prompter bertugas membuat ruang debat, menentukan mosi, memicu AI untuk mulai berbicara, mengatur jalannya ronde, hingga melakukan interogasi lanjutan kepada AI setelah sesi debat utama selesai.
    
*   **Moderator (Pengawas):** Bertindak sebagai asisten yang diangkat oleh Prompter. Moderator memiliki hak istimewa untuk membantu mengelola komentar pengunjung dan menjaga jalannya _live forum_ agar tetap kondusif.
    
*   **Audience (Pengunjung):** Pengamat (user biasa) yang dapat memasuki ruangan untuk membaca jalannya debat antar-AI secara _real-time_. Audience dapat berpartisipasi di _Live Chat_ antar sesama manusia dan memberikan _Like_ (❤️) pada argumen AI yang menjadi favorit mereka.
    

### ⚔️ Dua Mode Interaksi AI

*   **Mode Debat:** AI akan bertindak sebagai Tim Pro dan Tim Kontra, saling menyerang dan membantah logika secara bergantian.
    
*   **Mode Diskusi:** AI akan bertindak sebagai panelis kolaboratif, melanjutkan dan memperluas ide dari AI sebelumnya tanpa saling menjatuhkan.
    

### 🧠 Multi-Model AI Engine

Mendukung pergantian giliran secara otomatis di antara 5 model AI terkemuka: Gemini (Google), Llama 3.1 (Groq), Mistral, Cohere, dan Nemotron (OpenRouter).

### 🎙️ Sesi Post-Debat Terbuka (Unlimited Follow-up)

Fitur interogasi dinamis khusus untuk Prompter. Setelah kuota debat utama selesai, Prompter dapat menanyakan kejanggalan, meminta klarifikasi argumen, atau menyuruh AI merangkum kesimpulan tanpa batas ronde.

### 💬 Live Forum & Interaksi

Dilengkapi dengan _chat box_ untuk pengunjung (manusia) bersosialisasi dan sistem validasi asinkron (Ajax/Fetch) agar layar berjalan mulus tanpa perlu di-_refresh_.

🚀 3. Panduan Instalasi (Lokal)
-------------------------------

Aplikasi ini menggunakan framework Laravel dan basis data SQLite (sangat ringan, tidak perlu menginstal MySQL/XAMPP). Ikuti langkah berikut untuk menjalankannya di komputer:

### Tahap 1: Persiapan File

Buka Terminal / Command Prompt. Clone repository ke dalam folder lokal Anda dan masuk ke direktorinya:

Bash

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   git clone https://github.com/synapse-arena/synapse-arena.git  cd synapse-arena   `

### Tahap 2: Instalasi Dependensi

Instal library PHP yang dibutuhkan (pastikan Anda sudah menginstal Composer):

Bash

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   composer install   `

Instal dependensi _frontend_ (Tailwind/DaisyUI) (pastikan Anda sudah menginstal Node.js):

Bash

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   npm install  npm run build   `

### Tahap 3: Konfigurasi Lingkungan (.env)

Salin file .env.example menjadi file .env aktif, lalu buat kunci keamanan aplikasi:

Bash

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   cp .env.example .env  php artisan key:generate   `

Buka file .env di teks editor (seperti VS Code). Masukkan API KEY dari masing-masing _provider_ AI di baris paling bawah:

Ini, TOML

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   GEMINI_API_KEY=masukkan_key_anda_disini  GROQ_API_KEY=masukkan_key_anda_disini  MISTRAL_API_KEY=masukkan_key_anda_disini  COHERE_API_KEY=masukkan_key_anda_disini  OPENROUTER_API_KEY=masukkan_key_anda_disini   `

### Tahap 4: Basis Data & Menjalankan Mesin

Bangun struktur database SQLite Anda dan masukkan data awal (Seeder) untuk membuat akun demo:

Bash

Plain textANTLR4BashCC#CSSCoffeeScriptCMakeDartDjangoDockerEJSErlangGitGoGraphQLGroovyHTMLJavaJavaScriptJSONJSXKotlinLaTeXLessLuaMakefileMarkdownMATLABMarkupObjective-CPerlPHPPowerShell.propertiesProtocol BuffersPythonRRubySass (Sass)Sass (Scss)SchemeSQLShellSwiftSVGTSXTypeScriptWebAssemblyYAMLXML`   php artisan migrate  php artisan db:seed   `

**SANGAT PENTING:** Aplikasi ini membutuhkan proses yang berjalan secara bersamaan. Silakan buka **tab terminal yang berbeda** untuk menjalankan 3 perintah ini:

1.  php artisan serve
    
    *   **Penjelasan:** Perintah ini menyalakan server _development_ lokal bawaan Laravel. Tanpa ini, aplikasi web Anda tidak akan memiliki "rumah" dan tidak bisa diakses melalui URL browser (biasanya berjalan di http://localhost:8000).
        
2.  npm run dev
    
    *   **Penjelasan:** Perintah ini menjalankan server Vite (Node.js) yang bertugas mengompilasi aset _frontend_ seperti Tailwind CSS dan JavaScript secara _real-time_ (Hot Module Replacement). Perintah ini memastikan tampilan web dirender dengan sempurna dan setiap interaksi _Live Chat_ atau _Ajax_ berjalan mulus.
        
3.  php artisan queue:listen
    
    *   **Penjelasan:** Ini adalah **"Otak"** dari Synapse Arena. Karena proses meminta jawaban dari kelima model AI (Gemini, Llama, Mistral, dll) memakan waktu beberapa detik, proses ini tidak boleh menahan _loading browser_ pengguna. Perintah ini menyalakan _worker_ (pekerja latar belakang) yang akan mengantre dan mengeksekusi panggilan API ke AI di balik layar tanpa membuat web nge-_hang_.
        

Aplikasi sekarang dapat diakses melalui browser di: http://localhost:8000

🔐 4. Akun Default Demo (Hasil Seeder)
--------------------------------------

Untuk memudahkan pengujian aplikasi tanpa perlu mendaftar dari awal, Anda dapat langsung _login_ menggunakan kredensial hasil _seeder_ berikut berdasarkan _role_\-nya:

*   **Kreator (Prompter):** prompter@demo.com — Password: password
    
*   **Pengawas (Moderator):** moderator@demo.com — Password: password
    
*   **Pengunjung (Audience):** audience@demo.com — Password: password
    

🔑 5. Panduan Mendapatkan API Key AI
------------------------------------

Untuk menjalankan aplikasi ini secara maksimal, Anda memerlukan kunci akses (API Key) dari kelima penyedia layanan AI. Semuanya menyediakan kuota GRATIS untuk _developer/tier free_.

1.  **Gemini API Key (Google)**
    
    *   Kunjungi [Google AI Studio](https://aistudio.google.com).
        
    *   Login menggunakan akun Google Anda.
        
    *   Pilih menu "Get API key" di panel sebelah kiri.
        
    *   Klik "Create API key" dan salin kodenya ke .env (GEMINI\_API\_KEY).
        
2.  **Groq API Key (Llama 3.1)**
    
    *   Kunjungi [GroqCloud Console](https://console.groq.com).
        
    *   Buat akun atau login.
        
    *   Pilih menu "API Keys" di sidebar kiri.
        
    *   Klik "Create API Key", beri nama, dan salin kodenya ke .env (GROQ\_API\_KEY).
        
3.  **Mistral API Key**
    
    *   Kunjungi [Mistral La Plateforme](https://console.mistral.ai).
        
    *   Buat akun atau login.
        
    *   Masuk ke menu "API keys".
        
    *   Klik "Create new key", salin kodenya ke .env (MISTRAL\_API\_KEY).
        
4.  **Cohere API Key**
    
    *   Kunjungi [Cohere Dashboard](https://dashboard.cohere.com).
        
    *   Buat akun atau login.
        
    *   Di halaman utama dashboard, temukan bagian "API Keys".
        
    *   Gunakan Trial Key (Gratis) yang tersedia atau klik "Create API Key". Salin ke .env (COHERE\_API\_KEY).
        
5.  **OpenRouter API Key (NVIDIA Nemotron)**
    
    *   Kunjungi [OpenRouter](https://openrouter.ai).
        
    *   Login menggunakan akun Google atau GitHub Anda.
        
    *   Klik profil Anda di pojok kanan atas, lalu pilih "Keys".
        
    *   Klik "Create Key", beri nama (misal: Synapse), dan salin kodenya ke .env (OPENROUTER\_API\_KEY).