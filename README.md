AI Medical Triage Assistant

This project provides an API-driven medical triage assistant built with Laravel and powered by modern large language models.
The system analyzes patient symptoms and returns a structured assessment including severity, medical advice, and reasoning.
A simple frontend interface is included for testing and demonstration purposes.

Features

Symptom analysis using LLMs (Groq API)

Returns structured JSON: severity, advice, reason

Severity classification (low / medium / emergency)

Clean UI built with TailwindCSS

Saves triage records into the database

REST API endpoint for external integrations

Tech Stack

Laravel 12

PHP 8.4

MySQL

Groq API (Llama-3.3 models)

TailwindCSS

Installation
1. Clone the repository
git clone https://github.com/henryhkb/ai-medical-triage.git
cd ai-medical-triage

2. Install dependencies
composer install
npm install
npm run build

3. Create environment file
cp .env.example .env

4. Generate application key
php artisan key:generate

5. Configure the database

Edit your .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_triage
DB_USERNAME=root
DB_PASSWORD=yourpassword

6. Add your Groq API key
GROQ_API_KEY=your_api_key_here

7. Run migrations
php artisan migrate

8. Start the server
php artisan serve


The application will be available at:

http://localhost:8000

API Documentation
Endpoint: POST /api/triage

Analyzes patient symptoms and returns structured medical guidance.

Request Body
{
  "symptoms": "string"
}

Example
{
  "symptoms": "severe chest pain, sweating, shortness of breath"
}

Response
{
  "record": {
      "id": 12,
      "symptoms": "...",
      "severity": "emergency",
      "advice": "Seek immediate medical attention...",
      "reason": "...",
      "created_at": "2025-11-26",
      "updated_at": "2025-11-26"
  },
  "ai": {
      "severity": "emergency",
      "advice": "...",
      "reason": "..."
  }
}

Project Structure
ai-medical-triage/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── TriageController.php
│   ├── Models/
│   │   └── TriageRecord.php
│
├── routes/
│   └── api.php
│
├── resources/
│   └── views/
│       └── triage.blade.php
│
├── database/
│   └── migrations/
│       └── ****_create_triage_records_table.php
│
├── public/
│   └── index.php
│
├── composer.json
└── README.md


How It Works

The user enters symptoms in the UI or sends them via API.

Laravel forwards the symptoms to the Groq model.

The model returns structured JSON containing severity, advice, and reasoning.

The result is saved to the database.

The frontend displays severity with color-coded badges.

Use Cases

Medical triage prototypes

Health assistant research

AI-powered diagnostics demo

Educational and training tools

Contributing

Pull requests are welcome.
For major changes, please open an issue to discuss your ideas.

License

This project is available under the MIT License.
