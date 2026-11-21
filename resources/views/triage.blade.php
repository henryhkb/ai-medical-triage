<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TriageIQ · AI Symptom Triage</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } }
        };
    </script>

    <style>
        .fade-in { animation: fadeIn .35s ease-out both; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .chev { transform: rotate(180deg); }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            #view-assess { display: block !important; }
            #result-col { grid-column: 1 / -1 !important; }
            .print-flat { box-shadow: none !important; border: 0 !important; }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

<!-- ============ Header ============ -->
<header class="no-print sticky top-0 z-20 bg-white/85 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2.5 shrink-0">
            <span class="h-9 w-9 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 grid place-items-center text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2-5 4 10 2-5h6"/></svg>
            </span>
            <span class="text-lg font-extrabold tracking-tight">Triage<span class="text-indigo-600">IQ</span></span>
        </a>

        <nav class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm font-semibold">
            <button data-tab="assess" class="tab-btn px-3 sm:px-4 py-1.5 rounded-lg transition"><span class="sm:hidden">Assess</span><span class="hidden sm:inline">New assessment</span></button>
            <button data-tab="history" class="tab-btn px-3 sm:px-4 py-1.5 rounded-lg transition">History</button>
        </nav>
    </div>
</header>

<main class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <!-- ============ Assess view ============ -->
    <section id="view-assess">
        <div class="no-print mb-8">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-full px-3 py-1">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span> AI-assisted triage · {{ config('services.groq.model') }} via Groq
            </span>
            <h1 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">How urgent are these symptoms?</h1>
            <p class="mt-2 text-slate-600 max-w-2xl">Describe what the patient is experiencing. TriageIQ assesses urgency, recommends a next step and highlights warning signs in seconds.</p>
        </div>

        <div class="grid lg:grid-cols-5 gap-6 items-start">

            <!-- Form -->
            <div class="no-print lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 lg:sticky lg:top-24">
                <form id="triage-form" novalidate>
                    <label for="symptoms" class="block text-sm font-semibold text-slate-800">Symptoms</label>
                    <textarea id="symptoms" rows="5" maxlength="2000"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-y"
                        placeholder="e.g. Crushing chest pain spreading to the left arm, sweating and shortness of breath…"></textarea>
                    <div class="mt-1 flex justify-between text-xs text-slate-400">
                        <span class="hidden sm:inline">Press ⌘/Ctrl + Enter to analyze</span>
                        <span><span id="char-count">0</span>/2000</span>
                    </div>

                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Try an example</p>
                    <div id="examples" class="mt-2 flex flex-wrap gap-2"></div>

                    <details id="details-box" class="mt-5 rounded-xl border border-slate-200 bg-slate-50/60">
                        <summary class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700">
                            <span>Patient details <span class="font-normal text-slate-500">(optional, improves accuracy)</span></span>
                            <svg class="chev h-4 w-4 text-slate-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </summary>
                        <div class="grid grid-cols-2 gap-3 px-4 pb-4">
                            <label class="text-xs font-medium text-slate-600">Age
                                <input id="age" type="number" min="0" max="120" inputmode="numeric" placeholder="e.g. 45"
                                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </label>
                            <label class="text-xs font-medium text-slate-600">Sex
                                <select id="sex" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Not specified</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </label>
                            <label class="col-span-2 text-xs font-medium text-slate-600">How long have the symptoms lasted?
                                <select id="duration" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Not specified</option>
                                    <option>Less than 1 hour</option>
                                    <option>1–24 hours</option>
                                    <option>1–3 days</option>
                                    <option>4–7 days</option>
                                    <option>More than a week</option>
                                </select>
                            </label>
                            <label class="col-span-2 text-xs font-medium text-slate-600">Existing conditions or medications
                                <input id="conditions" type="text" maxlength="500" placeholder="e.g. diabetes, high blood pressure"
                                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </label>
                        </div>
                    </details>

                    <p id="form-error" class="hidden mt-3 text-sm text-red-600"></p>

                    <button id="submit-btn" type="submit"
                        class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-3 shadow-sm transition">
                        <span id="submit-label">Analyze symptoms</span>
                    </button>
                </form>
            </div>

            <!-- Result -->
            <div id="result-col" class="lg:col-span-3">
                <div id="result-panel" class="print-flat bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 min-h-[28rem]"></div>
            </div>
        </div>
    </section>

    <!-- ============ History view ============ -->
    <section id="view-history" class="hidden">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Assessment history</h1>
                <p class="mt-1 text-slate-600">Every assessment is saved. Open one to see the full result.</p>
            </div>
            <button id="refresh-btn" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Refresh
            </button>
        </div>

        <div id="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4"></div>
        <div id="distribution" class="mt-4"></div>

        <div class="mt-6 flex flex-wrap gap-2" id="filters"></div>
        <div id="history-list" class="mt-4 space-y-3"></div>
    </section>
</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 text-xs text-slate-500 flex flex-col sm:flex-row gap-2 sm:justify-between">
        <p><strong class="text-slate-700">Not medical advice.</strong> TriageIQ is a demonstration tool and does not replace a qualified clinician. In an emergency, call your local emergency number immediately.</p>
        <p class="shrink-0">© {{ date('Y') }} TriageIQ</p>
    </div>
</footer>

<script>
/* ---------------- Helpers ---------------- */
const $ = (id) => document.getElementById(id);
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const PATHS = {
    alert: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
    check: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    info: '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>',
    clock: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
    copy: '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>',
    print: '<path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>',
    plus: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
    user: '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>',
    sparkles: '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>',
    pulse: '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2-5 4 10 2-5h6"/>',
};
const icon = (name, cls = 'h-5 w-5') =>
    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="${cls}" aria-hidden="true">${PATHS[name]}</svg>`;

const SEVERITY = {
    emergency: { label: 'Emergency', card: 'bg-red-50 border-red-200', text: 'text-red-700', solid: 'bg-red-600', pill: 'bg-red-100 text-red-700 ring-red-600/20', icon: 'alert' },
    medium:    { label: 'Moderate',  card: 'bg-amber-50 border-amber-200', text: 'text-amber-700', solid: 'bg-amber-500', pill: 'bg-amber-100 text-amber-800 ring-amber-600/20', icon: 'clock' },
    low:       { label: 'Low',       card: 'bg-emerald-50 border-emerald-200', text: 'text-emerald-700', solid: 'bg-emerald-600', pill: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20', icon: 'check' },
    unknown:   { label: 'Unclear',   card: 'bg-slate-50 border-slate-200', text: 'text-slate-700', solid: 'bg-slate-500', pill: 'bg-slate-100 text-slate-700 ring-slate-500/20', icon: 'info' },
};
const sev = (s) => SEVERITY[s] || SEVERITY.unknown;

const NEXT_STEP = {
    emergency:   { label: 'Go to the emergency department now', sub: 'Call your local emergency number if symptoms are severe or getting worse.' },
    urgent_care: { label: 'Get seen within 24 hours', sub: 'Visit an urgent care centre or a same-day clinic.' },
    see_doctor:  { label: "Book a doctor's appointment", sub: 'Arrange a visit with a GP or clinic within the next few days.' },
    self_care:   { label: 'Manage at home', sub: 'Monitor the symptoms and seek care if they get worse.' },
};

const LIKELIHOOD = {
    high:   'bg-indigo-600 text-white',
    medium: 'bg-indigo-100 text-indigo-700',
    low:    'bg-slate-100 text-slate-600',
};

const EXAMPLES = [
    { label: 'Chest pain & sweating', symptoms: 'Crushing chest pain spreading to my left arm and jaw, sweating heavily and short of breath.', age: 58, sex: 'male', duration: 'Less than 1 hour', conditions: 'High blood pressure, smoker' },
    { label: 'Sudden face droop', symptoms: 'Right side of my face suddenly drooping, slurred speech and my right arm feels weak.', age: 67, sex: 'female', duration: 'Less than 1 hour', conditions: '' },
    { label: 'Child with fever', symptoms: 'High fever of 39.5°C, runny nose, coughing and not eating much. Still drinking fluids and playing a little.', age: 4, sex: '', duration: '1–3 days', conditions: '' },
    { label: 'Twisted ankle', symptoms: 'Twisted my ankle playing football. It is swollen and painful but I can still walk on it.', age: 24, sex: 'male', duration: '1–24 hours', conditions: '' },
    { label: 'Mild headache', symptoms: 'Mild headache and tired, dry eyes after a long day working at the computer.', age: 31, sex: 'female', duration: '1–24 hours', conditions: '' },
];

const timeAgo = (iso) => {
    if (!iso) return '';
    const s = Math.round((Date.now() - new Date(iso).getTime()) / 1000);
    if (s < 60) return 'just now';
    const m = Math.round(s / 60); if (m < 60) return `${m} min ago`;
    const h = Math.round(m / 60); if (h < 24) return `${h} hr${h > 1 ? 's' : ''} ago`;
    const d = Math.round(h / 24); if (d < 7) return `${d} day${d > 1 ? 's' : ''} ago`;
    return new Date(iso).toLocaleDateString();
};

const patientChips = (p = {}) => {
    const chips = [];
    if (p.age !== undefined && p.age !== null && p.age !== '') chips.push(`${esc(p.age)} yrs`);
    if (p.sex) chips.push(esc(p.sex[0].toUpperCase() + p.sex.slice(1)));
    if (p.duration) chips.push(esc(p.duration));
    if (p.conditions) chips.push(esc(p.conditions));
    return chips.map(c => `<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">${c}</span>`).join('');
};

/* ---------------- Result panel states ---------------- */
let current = null; // record currently shown

function renderEmpty() {
    current = null;
    $('result-panel').innerHTML = `
        <div class="h-full min-h-[24rem] flex flex-col items-center justify-center text-center px-4 fade-in">
            <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 grid place-items-center">${icon('pulse', 'h-7 w-7')}</div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Your assessment will appear here</h2>
            <p class="mt-1 text-sm text-slate-500 max-w-sm">Enter symptoms or pick an example, then press <span class="font-semibold text-slate-700">Analyze symptoms</span>.</p>
            <ol class="mt-8 grid sm:grid-cols-3 gap-3 w-full max-w-lg text-left">
                ${[['1', 'Describe', 'Symptoms and optional patient details'], ['2', 'Assess', 'AI rates urgency and explains why'], ['3', 'Act', 'Clear next step and warning signs']]
                    .map(([n, t, d]) => `<li class="rounded-xl border border-slate-200 p-3"><span class="text-xs font-bold text-indigo-600">STEP ${n}</span><p class="font-semibold text-slate-800 text-sm mt-0.5">${t}</p><p class="text-xs text-slate-500 mt-0.5">${d}</p></li>`).join('')}
            </ol>
        </div>`;
}

let loadingTimer = null;
function renderLoading() {
    const steps = ['Reading symptoms', 'Checking for warning signs', 'Assessing urgency', 'Preparing recommendations'];
    $('result-panel').innerHTML = `
        <div class="min-h-[24rem] flex flex-col items-center justify-center fade-in">
            <div class="relative h-16 w-16">
                <div class="absolute inset-0 rounded-full border-4 border-indigo-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-indigo-600 animate-spin"></div>
                <div class="absolute inset-0 grid place-items-center text-indigo-600">${icon('pulse', 'h-6 w-6')}</div>
            </div>
            <ul id="loading-steps" class="mt-6 space-y-2 text-sm">
                ${steps.map((s, i) => `<li data-i="${i}" class="flex items-center gap-2 text-slate-400 transition-colors"><span class="h-2 w-2 rounded-full bg-slate-300"></span>${s}</li>`).join('')}
            </ul>
        </div>`;
    let i = 0;
    const tick = () => {
        document.querySelectorAll('#loading-steps li').forEach(li => {
            const n = +li.dataset.i;
            li.className = `flex items-center gap-2 transition-colors ${n < i ? 'text-slate-500' : n === i ? 'text-slate-900 font-semibold' : 'text-slate-400'}`;
            li.firstElementChild.className = `h-2 w-2 rounded-full ${n < i ? 'bg-emerald-500' : n === i ? 'bg-indigo-600 animate-pulse' : 'bg-slate-300'}`;
        });
        if (i < steps.length - 1) i++;
    };
    tick();
    clearInterval(loadingTimer);
    loadingTimer = setInterval(tick, 900);
}

function renderError(message) {
    $('result-panel').innerHTML = `
        <div class="min-h-[24rem] flex flex-col items-center justify-center text-center fade-in">
            <div class="h-14 w-14 rounded-2xl bg-red-50 text-red-600 grid place-items-center">${icon('alert', 'h-7 w-7')}</div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Something went wrong</h2>
            <p class="mt-1 text-sm text-slate-600 max-w-sm">${esc(message)}</p>
            <button onclick="submitForm()" class="no-print mt-5 rounded-xl bg-slate-900 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 transition">Try again</button>
        </div>`;
}

function section(title, iconName, body, iconCls = 'text-indigo-600') {
    return `
        <section class="mt-6">
            <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                <span class="${iconCls}">${icon(iconName, 'h-4 w-4')}</span>${title}
            </h3>
            <div class="mt-2">${body}</div>
        </section>`;
}

function renderResult(record, { saved = false } = {}) {
    current = record;
    const ai = record.ai || {};
    const s = sev(ai.severity);
    const step = NEXT_STEP[ai.next_step] || null;
    const conf = Number.isInteger(ai.confidence) ? ai.confidence : null;
    const conditions = ai.possible_conditions || [];
    const redFlags = ai.red_flags || [];
    const selfCare = ai.self_care || [];

    $('result-panel').innerHTML = `
        <div class="fade-in">
            ${saved ? `<div class="no-print mb-4 flex items-center gap-2 text-xs font-medium text-slate-500">${icon('clock', 'h-4 w-4')} Saved assessment · ${esc(timeAgo(record.created_at))}</div>` : ''}

            ${ai.severity === 'emergency' ? `
            <div class="mb-5 rounded-xl bg-red-600 text-white p-4 flex gap-3 items-start shadow-sm">
                <span class="mt-0.5 shrink-0">${icon('alert', 'h-6 w-6')}</span>
                <div>
                    <p class="font-bold">Possible medical emergency</p>
                    <p class="text-sm text-red-50">Call your local emergency number or go to the nearest emergency department now. Do not drive yourself.</p>
                </div>
            </div>` : ''}

            <div class="rounded-2xl border ${s.card} p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="h-12 w-12 rounded-xl ${s.solid} text-white grid place-items-center shadow-sm">${icon(s.icon, 'h-6 w-6')}</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Urgency</p>
                            <p class="text-2xl font-extrabold ${s.text}">${s.label}</p>
                        </div>
                    </div>
                    ${conf !== null ? `
                    <div class="min-w-[9rem]">
                        <div class="flex justify-between text-xs font-semibold text-slate-500"><span>AI confidence</span><span class="text-slate-800">${conf}%</span></div>
                        <div class="mt-1.5 h-2 rounded-full bg-white/80 ring-1 ring-slate-200 overflow-hidden"><div class="h-full rounded-full ${s.solid}" style="width:${conf}%"></div></div>
                    </div>` : ''}
                </div>
                ${step ? `
                <div class="mt-4 rounded-xl bg-white/80 ring-1 ring-slate-200 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recommended next step</p>
                    <p class="mt-0.5 font-bold text-slate-900">${step.label}</p>
                    <p class="text-sm text-slate-600">${step.sub}</p>
                </div>` : ''}
            </div>

            ${ai.advice ? section('What to do', 'sparkles', `<p class="text-slate-700 leading-relaxed">${esc(ai.advice)}</p>`) : ''}
            ${ai.reason ? section('Why this level', 'info', `<p class="text-slate-700 leading-relaxed">${esc(ai.reason)}</p>`) : ''}

            ${conditions.length ? section('Possible causes to discuss with a clinician', 'user', `
                <ul class="grid sm:grid-cols-2 gap-2">
                    ${conditions.map(c => `
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2">
                        <span class="text-sm font-medium text-slate-800">${esc(c.name)}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide ${LIKELIHOOD[c.likelihood] || LIKELIHOOD.medium}">${esc(c.likelihood)}</span>
                    </li>`).join('')}
                </ul>
                <p class="mt-2 text-xs text-slate-500">These are possibilities, not a diagnosis.</p>`) : ''}

            ${(redFlags.length || selfCare.length) ? `
            <div class="mt-6 grid ${redFlags.length && selfCare.length ? 'sm:grid-cols-2' : ''} gap-4">
                ${redFlags.length ? `
                <div class="rounded-xl border border-red-200 bg-red-50/60 p-4">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-red-700">${icon('alert', 'h-4 w-4')} Get emergency help if</h3>
                    <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                        ${redFlags.map(f => `<li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>${esc(f)}</li>`).join('')}
                    </ul>
                </div>` : ''}
                ${selfCare.length ? `
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-emerald-700">${icon('check', 'h-4 w-4')} Self-care tips</h3>
                    <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                        ${selfCare.map(t => `<li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>${esc(t)}</li>`).join('')}
                    </ul>
                </div>` : ''}
            </div>` : ''}

            ${!ai.advice && !ai.reason ? `<p class="mt-6 text-slate-600">The AI did not return a usable assessment. Please try again with more detail.</p>` : ''}

            <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reported symptoms</p>
                <p class="mt-1 text-sm text-slate-700 italic">“${esc(record.symptoms)}”</p>
                ${patientChips(record.patient) ? `<div class="mt-2 flex flex-wrap gap-1.5">${patientChips(record.patient)}</div>` : ''}
            </div>

            <div class="no-print mt-6 flex flex-wrap gap-2">
                <button onclick="copySummary(this)" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">${icon('copy', 'h-4 w-4')}<span>Copy summary</span></button>
                <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">${icon('print', 'h-4 w-4')}Print</button>
                <button onclick="newAssessment()" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition">${icon('plus', 'h-4 w-4')}New assessment</button>
            </div>
            <p class="mt-4 text-xs text-slate-400">AI-generated guidance for demonstration only. Not a substitute for professional medical advice.</p>
        </div>`;
}

/* ---------------- Actions ---------------- */
function summaryText(r) {
    const ai = r.ai || {};
    const lines = [
        `TriageIQ assessment, ${new Date(r.created_at || Date.now()).toLocaleString()}`,
        `Urgency: ${sev(ai.severity).label}${Number.isInteger(ai.confidence) ? ` (AI confidence ${ai.confidence}%)` : ''}`,
    ];
    if (NEXT_STEP[ai.next_step]) lines.push(`Next step: ${NEXT_STEP[ai.next_step].label}`);
    lines.push(`Symptoms: ${r.symptoms}`);
    if (ai.advice) lines.push(`What to do: ${ai.advice}`);
    if (ai.reason) lines.push(`Why: ${ai.reason}`);
    if (ai.possible_conditions?.length) lines.push(`Possible causes: ${ai.possible_conditions.map(c => `${c.name} (${c.likelihood})`).join(', ')}`);
    if (ai.red_flags?.length) lines.push(`Get emergency help if: ${ai.red_flags.join('; ')}`);
    if (ai.self_care?.length) lines.push(`Self-care: ${ai.self_care.join('; ')}`);
    lines.push('', 'AI-generated guidance. Not medical advice.');
    return lines.join('\n');
}

async function copySummary(btn) {
    if (!current) return;
    try {
        await navigator.clipboard.writeText(summaryText(current));
        const label = btn.querySelector('span');
        label.textContent = 'Copied!';
        setTimeout(() => (label.textContent = 'Copy summary'), 1800);
    } catch {
        alert('Could not copy to the clipboard.');
    }
}

function newAssessment() {
    $('triage-form').reset();
    $('char-count').textContent = '0';
    renderEmpty();
    showTab('assess');
    $('symptoms').focus();
}

function fillExample(ex) {
    $('symptoms').value = ex.symptoms;
    $('age').value = ex.age ?? '';
    $('sex').value = ex.sex ?? '';
    $('duration').value = ex.duration ?? '';
    $('conditions').value = ex.conditions ?? '';
    $('char-count').textContent = ex.symptoms.length;
    $('details-box').open = true;
    $('form-error').classList.add('hidden');
}

let busy = false;
async function submitForm() {
    if (busy) return;
    const symptoms = $('symptoms').value.trim();
    if (symptoms.length < 3) {
        $('form-error').textContent = 'Please describe the symptoms first.';
        $('form-error').classList.remove('hidden');
        $('symptoms').focus();
        return;
    }
    $('form-error').classList.add('hidden');

    const payload = { symptoms };
    const age = $('age').value.trim();
    if (age !== '') payload.age = Number(age);
    if ($('sex').value) payload.sex = $('sex').value;
    if ($('duration').value) payload.duration = $('duration').value;
    if ($('conditions').value.trim()) payload.conditions = $('conditions').value.trim();

    busy = true;
    $('submit-btn').disabled = true;
    $('submit-label').textContent = 'Analyzing…';
    renderLoading();
    if (window.innerWidth < 1024) $('result-col').scrollIntoView({ behavior: 'smooth', block: 'start' });

    try {
        const res = await fetch('/api/triage', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = res.status === 429
                ? 'Too many requests. Please wait a minute and try again.'
                : (data.error || data.message || `Request failed (${res.status}).`);
            renderError(msg + (data.detail ? ` (${data.detail})` : ''));
            return;
        }
        renderResult(data.record);
        historyCache = null;
    } catch (err) {
        renderError('Could not reach the server. Check that the app is running.');
    } finally {
        clearInterval(loadingTimer);
        busy = false;
        $('submit-btn').disabled = false;
        $('submit-label').textContent = 'Analyze symptoms';
    }
}

/* ---------------- History ---------------- */
let historyCache = null;
let historyFilter = 'all';

async function loadHistory(force = false) {
    if (historyCache && !force) return renderHistory();
    $('history-list').innerHTML = `<div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500 text-sm">Loading…</div>`;
    try {
        const res = await fetch('/api/triage/history', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error();
        historyCache = await res.json();
        renderHistory();
    } catch {
        $('history-list').innerHTML = `<div class="rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-red-700 text-sm">Could not load history.</div>`;
    }
}

function renderHistory() {
    const { stats, records } = historyCache;
    const tiles = [
        ['Total assessments', stats.total, 'bg-indigo-600'],
        ['Emergency', stats.emergency, 'bg-red-600'],
        ['Moderate', stats.medium, 'bg-amber-500'],
        ['Low', stats.low, 'bg-emerald-600'],
    ];
    $('stats').innerHTML = tiles.map(([label, value, dot]) => `
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="flex items-center gap-2 text-sm font-medium text-slate-500"><span class="h-2.5 w-2.5 rounded-full ${dot}"></span>${label}</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">${value}</p>
        </div>`).join('');

    const known = stats.emergency + stats.medium + stats.low;
    $('distribution').innerHTML = known ? `
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-700">Urgency mix</p>
            <div class="mt-3 flex h-3 w-full overflow-hidden rounded-full bg-slate-100 gap-0.5">
                ${[['emergency', 'bg-red-600'], ['medium', 'bg-amber-500'], ['low', 'bg-emerald-600']]
                    .filter(([k]) => stats[k] > 0)
                    .map(([k, c]) => `<div class="${c}" style="width:${(stats[k] / known) * 100}%" title="${sev(k).label}: ${stats[k]}"></div>`).join('')}
            </div>
            <div class="mt-2 flex flex-wrap gap-4 text-xs text-slate-500">
                ${['emergency', 'medium', 'low'].map(k => `<span>${sev(k).label} ${Math.round((stats[k] / known) * 100)}%</span>`).join('')}
            </div>
        </div>` : '';

    const filters = [['all', 'All'], ['emergency', 'Emergency'], ['medium', 'Moderate'], ['low', 'Low']];
    $('filters').innerHTML = filters.map(([key, label]) => `
        <button data-filter="${key}" class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition ${historyFilter === key ? 'bg-slate-900 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50'}">${label}</button>`).join('');

    const rows = records.filter(r => historyFilter === 'all' || r.ai.severity === historyFilter);
    if (!rows.length) {
        $('history-list').innerHTML = `
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                <p class="font-semibold text-slate-700">No assessments ${historyFilter === 'all' ? 'yet' : 'in this category'}</p>
                <p class="mt-1 text-sm text-slate-500">Run an assessment and it will show up here.</p>
            </div>`;
        return;
    }
    $('history-list').innerHTML = rows.map(r => {
        const s = sev(r.ai.severity);
        return `
        <button data-id="${r.id}" class="history-row w-full text-left rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:shadow transition flex items-start gap-4">
            <span class="mt-1 h-3 w-3 shrink-0 rounded-full ${s.solid}"></span>
            <span class="flex-1 min-w-0">
                <span class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold ring-1 ring-inset ${s.pill}">${s.label}</span>
                    ${NEXT_STEP[r.ai.next_step] ? `<span class="text-xs text-slate-500">${NEXT_STEP[r.ai.next_step].label}</span>` : ''}
                </span>
                <span class="mt-1.5 block text-sm text-slate-800 truncate">${esc(r.symptoms)}</span>
                <span class="mt-1.5 flex flex-wrap gap-1.5">${patientChips(r.patient)}</span>
            </span>
            <span class="shrink-0 text-xs text-slate-400 whitespace-nowrap">${esc(timeAgo(r.created_at))}</span>
        </button>`;
    }).join('');
}

/* ---------------- Tabs ---------------- */
function showTab(tab) {
    $('view-assess').classList.toggle('hidden', tab !== 'assess');
    $('view-history').classList.toggle('hidden', tab !== 'history');
    document.querySelectorAll('.tab-btn').forEach(b => {
        const active = b.dataset.tab === tab;
        b.className = `tab-btn px-3 sm:px-4 py-1.5 rounded-lg transition ${active ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'}`;
    });
    const hash = tab === 'history' ? '#history' : '';
    if (location.hash !== hash) history.replaceState(null, '', location.pathname + hash);
    if (tab === 'history') loadHistory();
}

/* ---------------- Wire up ---------------- */
$('examples').innerHTML = EXAMPLES.map((ex, i) => `
    <button type="button" data-example="${i}" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:border-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition">${esc(ex.label)}</button>`).join('');

$('examples').addEventListener('click', e => {
    const b = e.target.closest('[data-example]');
    if (b) fillExample(EXAMPLES[+b.dataset.example]);
});
$('triage-form').addEventListener('submit', e => { e.preventDefault(); submitForm(); });
$('symptoms').addEventListener('input', e => { $('char-count').textContent = e.target.value.length; });
$('symptoms').addEventListener('keydown', e => {
    if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) { e.preventDefault(); submitForm(); }
});
document.querySelectorAll('.tab-btn').forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
$('refresh-btn').addEventListener('click', () => loadHistory(true));
$('filters').addEventListener('click', e => {
    const b = e.target.closest('[data-filter]');
    if (b) { historyFilter = b.dataset.filter; renderHistory(); }
});
$('history-list').addEventListener('click', e => {
    const b = e.target.closest('.history-row');
    if (!b) return;
    const rec = historyCache.records.find(r => r.id === +b.dataset.id);
    if (rec) { showTab('assess'); renderResult(rec, { saved: true }); window.scrollTo({ top: 0, behavior: 'smooth' }); }
});

renderEmpty();
showTab(location.hash === '#history' ? 'history' : 'assess');
</script>
</body>
</html>
