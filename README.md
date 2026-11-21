AI Medical Triage Assistant

This project provides an API-driven medical triage assistant built with Laravel and powered by modern large language models.
The system analyzes patient symptoms and returns a structured assessment including severity, medical advice, and reasoning.
A simple frontend interface is included for testing and demonstration purposes.

Features

TriageIQ web UI at / : symptom form, one-click example cases, optional patient details (age, sex, duration, conditions)

Rich assessment: urgency, recommended next step, possible causes, red-flag warnings, self-care tips, AI confidence

History tab with urgency stats and saved assessments you can reopen

Copy summary and print actions

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

Groq API (openai/gpt-oss-120b by default, set with GROQ_MODEL)

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

5. Configure the database (create it first)

mysql -u root -p -e "CREATE DATABASE medical_triage_api"


Edit your .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_triage_api
DB_USERNAME=root
DB_PASSWORD=yourpassword

6. Add your Groq API key
GROQ_API_KEY=your_api_key_here

7. Run migrations
php artisan migrate

8. Start the server
php artisan serve


The UI is available at:

http://localhost:8000/triage-ui

API Documentation
Endpoint: POST /api/triage

Analyzes patient symptoms and returns structured medical guidance.

Request Body
{
  "symptoms": "string (required, max 2000)",
  "age": 45,                      // optional, 0-120
  "sex": "female|male|other",     // optional
  "duration": "1–24 hours",       // optional
  "conditions": "diabetes"        // optional
}

Example
{
  "symptoms": "severe chest pain, sweating, shortness of breath"
}

Response
{
  "record": {
      "id": 12,
      "user_id": null,
      "symptoms": "...",
      "severity": "emergency",
      "advice": "Seek immediate medical attention...",
      "reason": "...",
      "analysis": "{ raw model JSON }",
      "created_at": "2025-11-26T21:00:00.000000Z",
      "updated_at": "2025-11-26T21:00:00.000000Z"
  },
  "ai": {
      "severity": "emergency",
      "advice": "...",
      "reason": "..."
  }
}

severity is always one of: low, medium, emergency, unknown.
The endpoint is rate limited to 20 requests per minute per client.

Endpoint: GET /api/triage/history

Returns urgency counts and the 50 most recent assessments:
{ "stats": { "total", "emergency", "medium", "low" }, "records": [ ... ] }

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
