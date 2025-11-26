<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Medical Triage Assistant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="max-w-2xl w-full bg-white rounded-2xl shadow-lg p-8">
    <h1 class="text-3xl font-extrabold text-center mb-6 text-gray-800">
        AI Medical Triage Assistant
    </h1>

    <!-- Symptoms input -->
    <label class="block text-gray-700 font-medium mb-2">Describe Patient Symptoms</label>
    <textarea 
        id="symptoms"
        rows="4"
        class="w-full border rounded-lg p-3 text-gray-800 focus:ring-2 focus:ring-blue-400 transition"
        placeholder="e.g. severe chest pain, sweating, shortness of breath..."
    ></textarea>

    <!-- Analyze button -->
    <button 
        onclick="analyzeSymptoms()"
        class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition"
    >
        Analyze Symptoms
    </button>

    <!-- Loading state -->
    <div id="loading" class="hidden text-center mt-6">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500 border-solid mx-auto"></div>
        <p class="text-gray-600 mt-3">Analyzing symptoms...</p>
    </div>

    <!-- Result box -->
    <div id="result" class="hidden mt-6 p-5 rounded-xl border bg-gray-50"></div>

</div>

<!-- Frontend logic -->
<script>
    function severityBadge(level) {
        const base = "px-3 py-1 rounded-full text-sm font-semibold";

        const badges = {
            emergency: `bg-red-600 text-white ${base}`,
            medium:    `bg-yellow-500 text-white ${base}`,
            low:       `bg-green-600 text-white ${base}`,
        };

        const key = (level || 'low').toLowerCase();
        return `<span class="${badges[key] || badges.low}">${key.toUpperCase()}</span>`;
    }

    function detectSeverity(text) {
        if (!text) return "low";
        let t = text.toLowerCase();

        if (t.includes("emergency") || t.includes("critical") || t.includes("urgent"))
            return "emergency";

        if (t.includes("medium") || t.includes("moderate"))
            return "medium";

        return "low";
    }

    function analyzeSymptoms() {
        let symptoms = document.getElementById("symptoms").value.trim();
        if (!symptoms) {
            alert("Please enter symptoms.");
            return;
        }

        // Reset displays
        document.getElementById("loading").classList.remove("hidden");
        document.getElementById("result").classList.add("hidden");

        fetch("/api/triage", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ symptoms })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById("loading").classList.add("hidden");
            document.getElementById("result").classList.remove("hidden");

            // ---------------- CORE FIX STARTS HERE ----------------
            let severity = "low";
            let advice   = "";
            let reason   = "";
            let fallbackText = "";

            // 1) If backend sends `ai` object (like in your Postman output)
            if (data.ai && typeof data.ai === 'object') {
                severity = (data.ai.severity || 'low').toLowerCase();
                advice   = data.ai.advice || "";
                reason   = data.ai.reason || "";
                fallbackText = advice || reason || "";
            }
            // 2) Else if backend sends `analysis` as JSON string
            else if (data.analysis) {
                let raw = data.analysis;

                try {
                    const parsed = (typeof raw === 'string') ? JSON.parse(raw) : raw;

                    severity = (parsed.severity || 'low').toLowerCase();
                    advice   = parsed.advice || "";
                    reason   = parsed.reason || "";
                    fallbackText = advice || reason || raw;
                } catch (e) {
                    // Not valid JSON, treat as plain text
                    fallbackText = raw;
                    severity = detectSeverity(raw);
                }
            }
            // 3) Nothing useful returned
            else {
                fallbackText = "No analysis returned.";
            }

            // If severity is still unknown/empty, try to detect from text
            if (!severity || severity === 'unknown') {
                severity = detectSeverity(advice || reason || fallbackText);
            }

            // Build nice HTML
            document.getElementById("result").innerHTML = `
                <h2 class="text-xl font-bold mb-3 text-gray-800">Triage Result</h2>
                ${severityBadge(severity)}
                <div class="mt-4 space-y-3 text-gray-700 leading-relaxed">
                    ${
                        advice
                            ? `<p><span class="font-semibold">Advice:</span> ${advice}</p>`
                            : ""
                    }
                    ${
                        reason
                            ? `<p><span class="font-semibold">Reason:</span> ${reason}</p>`
                            : ""
                    }
                    ${
                        !advice && !reason && fallbackText
                            ? `<p>${fallbackText}</p>`
                            : ""
                    }
                </div>
            `;
            // ---------------- CORE FIX ENDS HERE ----------------
        })
        .catch(err => {
            document.getElementById("loading").classList.add("hidden");
            alert("Error connecting to API: " + err);
        });
    }
</script>



</body>
</html>
