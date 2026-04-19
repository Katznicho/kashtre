<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-[#011478]">Automated Test</h1>
                    <p class="text-gray-600 mt-2">Complete user journey: Register → Order Items → Make Payment → Queue Items</p>
                </div>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 underline">← Back to Dashboard</a>
            </div>

            <!-- Test Setup Section -->
            <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Configure Test</h2>
                    
                    <!-- Payment Phone Input -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Phone Number</label>
                        <input type="tel" 
                               id="payment-phone" 
                               placeholder="e.g., 0777123456"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Items Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Items to Order</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50">
                            <div id="items-list" class="space-y-3">
                                <div class="text-center text-gray-500 py-4">
                                    <div class="animate-pulse">Loading items...</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            Selected: <span id="selected-count" class="font-semibold">0</span> items
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <strong>What This Test Does:</strong> Registers a user → Orders selected items → Processes payment → Queues items for delivery
                    </div>
                    <button onclick="runTests()" 
                            id="run-button"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition duration-200 flex items-center space-x-2 ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Run Test</span>
                    </button>
                </div>
            </div>

            <!-- Test Output Section -->
            <div id="test-output" class="bg-white rounded-xl shadow-sm p-6" style="display:none;">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-gray-900">Test Progress</h2>
                    <button onclick="clearOutput()" class="text-sm text-gray-600 hover:text-gray-900">Clear</button>
                </div>
                <div id="output-content" class="bg-gray-900 text-green-400 p-4 rounded font-mono text-sm overflow-auto max-h-96 whitespace-pre-wrap break-words border border-gray-700">
                    <!-- Test output will appear here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedItems = [];
        let allItems = [];

        // Load items on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadItems();
        });

        function loadItems() {
            fetch('{{ route("automated-tests.items") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load items');
                    }
                    return response.json();
                })
                .then(data => {
                    allItems = data;
                    renderItems();
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    document.getElementById('items-list').innerHTML = '<div class="text-red-500 p-4">❌ Error loading items. Please refresh the page.</div>';
                });
        }

        function renderItems() {
            const itemsList = document.getElementById('items-list');
            if (!allItems || allItems.length === 0) {
                itemsList.innerHTML = '<div class="text-gray-500 py-4 text-center">No items available in your business</div>';
                return;
            }

            itemsList.innerHTML = allItems.map(item => `
                <label class="flex items-center space-x-3 p-3 hover:bg-white rounded cursor-pointer hover:border border-gray-200">
                    <input type="checkbox" 
                           value="${item.id}"
                           onchange="toggleItem(${item.id}, '${escapeQuote(item.name)}')"
                           class="w-4 h-4 text-blue-600 rounded">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">${item.name}</div>
                        <div class="text-sm text-gray-600">${item.price.toLocaleString()} UGX</div>
                    </div>
                </label>
            `).join('');
        }

        function toggleItem(id, name) {
            const index = selectedItems.indexOf(id);
            if (index > -1) {
                selectedItems.splice(index, 1);
            } else {
                selectedItems.push(id);
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            document.getElementById('selected-count').textContent = selectedItems.length;
        }

        function runTests() {
            const button = document.getElementById('run-button');
            const outputSection = document.getElementById('test-output');
            const outputContent = document.getElementById('output-content');
            const paymentPhone = document.getElementById('payment-phone').value;
            
            if (!paymentPhone.trim()) {
                alert('Please enter a payment phone number');
                return;
            }

            if (selectedItems.length === 0) {
                alert('Please select at least one item');
                return;
            }

            // Disable button and show output section
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            outputSection.style.display = 'block';
            outputContent.innerHTML = '<span class="text-yellow-400">🔄 Starting test...\n</span>';

            // Call the backend to run tests
            fetch('{{ route("automated-tests.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    payment_phone: paymentPhone,
                    items: selectedItems
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const lines = data.output.split('\n');
                    let html = '';
                    
                    for (const line of lines) {
                        if (line.includes('✅')) {
                            html += '<div class="text-green-400">' + escapeHtml(line) + '</div>';
                        } else if (line.includes('❌')) {
                            html += '<div class="text-red-400">' + escapeHtml(line) + '</div>';
                        } else if (line.includes('STEP') || line.includes('🧪') || line.includes('📍') || line.includes('📊')) {
                            html += '<div class="text-cyan-400">' + escapeHtml(line) + '</div>';
                        } else if (line.includes('---') || line.includes('===')) {
                            html += '<div class="text-gray-500">' + escapeHtml(line) + '</div>';
                        } else if (line.trim()) {
                            html += '<div>' + escapeHtml(line) + '</div>';
                        } else {
                            html += '<div>&nbsp;</div>';
                        }
                    }
                    
                    outputContent.innerHTML = html;
                } else {
                    outputContent.innerHTML = '<span class="text-red-400">❌ Error: ' + (data.message || 'Unknown error') + '</span>\n' + (data.output || '');
                }
                
                // Re-enable button
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            })
            .catch(error => {
                outputContent.innerHTML = '<span class="text-red-400">❌ Error: ' + error.message + '</span>';
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }

        function clearOutput() {
            document.getElementById('test-output').style.display = 'none';
            document.getElementById('output-content').innerHTML = '';
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function escapeQuote(text) {
            return text.replace(/'/g, "\\'");
        }
    </script>
</x-app-layout>
