<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div>
                    <h2 class="text-3xl font-bold text-[#011478]">🧪 Comprehensive System Tests</h2>
                    <p class="text-gray-600 text-sm mt-2">Test entire system functionality: finances, queueing, packages, and accounting</p>
                </div>
                <a href="{{ route('dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold transition">
                    ← Back
                </a>
            </div>

            <!-- Test Business & Branch Info -->
            <div class="mb-6 grid grid-cols-2 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-600 font-semibold uppercase">Test Business</p>
                    <p class="text-lg font-bold text-blue-900">{{ $testBusiness->name }}</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $testBusiness->shortcode }}</p>
                </div>
                <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-4">
                    <p class="text-sm text-cyan-600 font-semibold uppercase">Test Branch</p>
                    <p class="text-lg font-bold text-cyan-900">{{ $testBranch->name }}</p>
                    <p class="text-xs text-cyan-600 mt-1">Branch ID: {{ $testBranch->id }}</p>
                </div>
            </div>

            <!-- Controls -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Run Tests</h3>
                    <button id="run-all-tests-btn" 
                            class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-6 py-3 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Run All Tests</span>
                    </button>
                </div>

                <div class="text-sm text-gray-600 space-y-2">
                    <p>✅ Create credit-eligible test client</p>
                    <p>✅ Create invoice with items</p>
                    <p>✅ Process payment transaction</p>
                    <p>✅ Verify accounting integrity</p>
                    <p>✅ Validate all database records</p>
                </div>
            </div>

            <!-- Test Output -->
            <div id="test-output" class="mb-6 hidden bg-gray-900 text-gray-100 rounded-lg shadow-sm p-6 font-mono text-sm overflow-auto max-h-96">
                <div id="test-log" class="space-y-1"></div>
            </div>

            <!-- Results Section -->
            <div id="results-section" class="hidden">
                <!-- Summary -->
                <div class="mb-6 grid grid-cols-3 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-600 font-semibold uppercase">Passed</p>
                        <p id="result-passed" class="text-3xl font-bold text-green-900">0</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-600 font-semibold uppercase">Failed</p>
                        <p id="result-failed" class="text-3xl font-bold text-red-900">0</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-600 font-semibold uppercase">Duration</p>
                        <p id="result-duration" class="text-3xl font-bold text-blue-900">0s</p>
                    </div>
                </div>

                <!-- Detailed Results -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h3>
                    <div id="detailed-results" class="space-y-3">
                        <!-- Results will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Status Message -->
            <div id="status-message" class="hidden mb-6 p-4 rounded-lg">
                <!-- Status will be inserted here -->
            </div>
        </div>
    </div>

    <script>
        const runBtn = document.getElementById('run-all-tests-btn');
        const testOutput = document.getElementById('test-output');
        const testLog = document.getElementById('test-log');
        const resultsSection = document.getElementById('results-section');
        const statusMessage = document.getElementById('status-message');

        runBtn.addEventListener('click', async () => {
            // Reset UI
            testOutput.classList.remove('hidden');
            resultsSection.classList.add('hidden');
            statusMessage.classList.add('hidden');
            testLog.innerHTML = '';
            runBtn.disabled = true;

            logMessage('🚀 Starting comprehensive system tests...');
            await delay(500);

            try {
                const response = await fetch('{{ route("system-tests.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({}),
                });

                const data = await response.json();

                if (data.success) {
                    logMessage('✅ Tests completed successfully!');
                    await delay(500);
                    displayResults(data.results);
                } else {
                    logMessage('❌ Tests failed: ' + data.error);
                    showStatus('error', 'Test Failed', data.error);
                }
            } catch (error) {
                logMessage('❌ Error: ' + error.message);
                showStatus('error', 'Error', error.message);
            } finally {
                runBtn.disabled = false;
            }
        });

        function logMessage(message) {
            const line = document.createElement('div');
            line.textContent = message;
            testLog.appendChild(line);
            testOutput.scrollTop = testOutput.scrollHeight;
        }

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function displayResults(results) {
            // Update summary
            const summary = results.summary;
            document.getElementById('result-passed').textContent = summary.passed;
            document.getElementById('result-failed').textContent = summary.failed;
            document.getElementById('result-duration').textContent = results.duration_seconds + 's';

            // Display detailed results
            const detailedResults = document.getElementById('detailed-results');
            detailedResults.innerHTML = '';

            results.tests.forEach(test => {
                const resultDiv = document.createElement('div');
                resultDiv.className = test.passed 
                    ? 'border-l-4 border-green-500 bg-green-50 p-4 rounded'
                    : 'border-l-4 border-red-500 bg-red-50 p-4 rounded';

                resultDiv.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold ${test.passed ? 'text-green-900' : 'text-red-900'}">
                                ${test.passed ? '✅' : '❌'} ${test.name}
                            </p>
                            <p class="text-sm ${test.passed ? 'text-green-700' : 'text-red-700'} mt-1">
                                ${test.message}
                            </p>
                        </div>
                    </div>
                `;
                detailedResults.appendChild(resultDiv);
            });

            resultsSection.classList.remove('hidden');

            // Show completion message
            if (summary.failed === 0) {
                showStatus('success', 'All Tests Passed! 🎉', 'System is functioning correctly.');
            } else {
                showStatus('warning', 'Some Tests Failed', `${summary.failed} test(s) failed. Review details above.`);
            }
        }

        function showStatus(type, title, message) {
            const colors = {
                success: 'bg-green-50 border-green-200 text-green-900',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-900',
                error: 'bg-red-50 border-red-200 text-red-900',
            };

            statusMessage.className = `border-l-4 p-4 rounded-lg ${colors[type]}`;
            statusMessage.innerHTML = `
                <p class="font-semibold">${title}</p>
                <p class="text-sm mt-1">${message}</p>
            `;
            statusMessage.classList.remove('hidden');
        }
    </script>
</x-app-layout>
