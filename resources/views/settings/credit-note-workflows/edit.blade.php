<x-app-layout>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('credit-note-workflows.index') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                    </svg>
                                    <span class="sr-only">Refund Workflows</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-4 text-sm.font-medium text-gray-500">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Edit Refund Workflow
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $creditNoteWorkflow->business->name }}
                </p>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form -->
        <div class="mt-8">
            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('credit-note-workflows.update', $creditNoteWorkflow) }}" method="POST" class="px-4 py-5 sm:p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Business -->
                    <div>
                        <label for="business_id" class="block text-sm font-medium text-gray-700">
                            Business <span class="text-red-500">*</span>
                        </label>
                        <select name="business_id" id="business_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('business_id') border-red-300 @enderror">
                            <option value="">Select a business...</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}" {{ old('business_id', $creditNoteWorkflow->business_id) == $business->id ? 'selected' : '' }}>
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('business_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('approver_user_ids.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('staff_selection')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Approvers -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="approver_user_ids" class="block text-sm font-medium text-gray-700">Approvers <span class="text-red-500">*</span></label>
                            <span class="text-xs text-gray-500">Select 1&ndash;3 people. Hold Cmd/Ctrl to pick multiple.</span>
                        </div>
                        <select multiple size="6" id="approver_user_ids" name="approver_user_ids[]"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('approver_user_ids') border-red-300 @enderror" disabled>
                            <option value="" disabled>Select a business first...</option>
                        </select>
                        @error('approver_user_ids')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('authorizer_user_ids.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-dashed border-gray-200"></div>

                    <!-- Authorizers -->
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="authorizer_user_ids" class="block text-sm font-medium text-gray-700">Authorizers <span class="text-red-500">*</span></label>
                            <span class="text-xs text-gray-500">Select 1&ndash;3 people. Hold Cmd/Ctrl to pick multiple.</span>
                        </div>
                        <select multiple size="6" id="authorizer_user_ids" name="authorizer_user_ids[]"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('authorizer_user_ids') border-red-300 @enderror" disabled>
                            <option value="" disabled>Select a business first...</option>
                        </select>
                        @error('authorizer_user_ids')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('service_point_supervisors.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-dashed border-gray-200"></div>

                    <!-- Service Points -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Service Points</h3>
                        <p class="mt-1 text-sm text-gray-500">Assign 1&ndash;4 supervisors to every service point. These rows mirror the spreadsheet.</p>
                        <div id="service-points-section" class="mt-4 space-y-4 hidden">
                            <div id="service-points-container" class="space-y-4"></div>
                        </div>
                        <p id="service-points-placeholder" class="mt-2 text-sm text-gray-400">
                            Select a business to load service points.
                        </p>
                        @error('service_point_supervisors')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Optional Default Supervisor -->
                    <div>
                        <label for="default_supervisor_user_id" class="block text-sm font-medium text-gray-700">Optional Fallback Supervisor</label>
                        <select name="default_supervisor_user_id" id="default_supervisor_user_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('default_supervisor_user_id') border-red-300 @enderror" disabled>
                            <option value="">Select a business first...</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Used only if a service point has no direct supervisors.</p>
                        @error('default_supervisor_user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $creditNoteWorkflow->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('credit-note-workflows.index') }}"
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Workflow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const MAX_APPROVERS = 3;
        const MAX_AUTHORIZERS = 3;
        const MAX_SUPERVISORS = 4;

        const businessSelect = document.getElementById('business_id');
        const approverSelect = document.getElementById('approver_user_ids');
        const authorizerSelect = document.getElementById('authorizer_user_ids');
        const defaultSupervisorSelect = document.getElementById('default_supervisor_user_id');
        const servicePointsSection = document.getElementById('service-points-section');
        const servicePointsContainer = document.getElementById('service-points-container');
        const servicePointsPlaceholder = document.getElementById('service-points-placeholder');

        const allUsers = @json($allUsers);
        const allServicePoints = @json($allServicePoints);
        const initialApproverIds = new Set((@json(old('approver_user_ids', $existingApproverIds)) || []).map(Number));
        const initialAuthorizerIds = new Set((@json(old('authorizer_user_ids', $existingAuthorizerIds)) || []).map(Number));
        let initialSupervisorMap = (() => {
            const raw = @json(old('service_point_supervisors', $servicePointAssignments)) || {};
            const map = new Map();
            Object.entries(raw).forEach(([servicePointId, value]) => {
                const selectedValues = Array.isArray(value) ? value : Object.values(value || {});
                map.set(Number(servicePointId), new Set(selectedValues.map(Number)));
            });
            return map;
        })();
        const initialDefaultSupervisorId = Number(@json(old('default_supervisor_user_id', $creditNoteWorkflow->default_supervisor_user_id)) || 0) || null;

        let approverSelection = new Set(initialApproverIds);
        let authorizerSelection = new Set(initialAuthorizerIds);
        let supervisorSelections = new Map(initialSupervisorMap);
        let defaultSupervisorSelection = initialDefaultSupervisorId;

        function getSelectedValues(selectElement) {
            return new Set(Array.from(selectElement.selectedOptions).map((opt) => Number(opt.value)));
        }

        function applySelection(selectElement, selectedSet) {
            Array.from(selectElement.options).forEach((option) => {
                if (!option.value) {
                    return;
                }
                option.selected = selectedSet.has(Number(option.value));
            });
        }

        function populateMulti(selectElement, users, selectedSet) {
            selectElement.innerHTML = '';

            if (!users.length) {
                const option = document.createElement('option');
                option.value = '';
                option.disabled = true;
                option.textContent = 'No staff available for this business';
                selectElement.appendChild(option);
                selectElement.disabled = true;
                return;
            }

            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                if (selectedSet.has(Number(user.id))) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });

            selectElement.disabled = false;
        }

        function populateSingle(selectElement, users, selectedId) {
            selectElement.innerHTML = '<option value="">No fallback supervisor</option>';

            if (!users.length) {
                selectElement.disabled = true;
                selectElement.classList.add('bg-gray-100');
                return;
            }

            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                if (selectedId && Number(user.id) === Number(selectedId)) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });

            selectElement.disabled = false;
            selectElement.classList.remove('bg-gray-100');
        }

        function syncRoleExclusion() {
            Array.from(approverSelect.options).forEach((option) => {
                if (!option.value) {
                    return;
                }
                const value = Number(option.value);
                option.disabled = !approverSelection.has(value) && authorizerSelection.has(value);
            });

            Array.from(authorizerSelect.options).forEach((option) => {
                if (!option.value) {
                    return;
                }
                const value = Number(option.value);
                option.disabled = !authorizerSelection.has(value) && approverSelection.has(value);
            });
        }

        function captureCurrentServicePointSelections() {
            const map = new Map();
            document.querySelectorAll('[data-service-point-id]').forEach((wrapper) => {
                const servicePointId = Number(wrapper.dataset.servicePointId);
                const select = wrapper.querySelector('select');
                if (!select) {
                    return;
                }
                map.set(servicePointId, getSelectedValues(select));
            });
            return map;
        }

        function enforceSupervisorLimit(selectElement, servicePointId) {
            const previousSelection = supervisorSelections.get(servicePointId) || new Set();
            const currentSelection = getSelectedValues(selectElement);

            if (currentSelection.size > MAX_SUPERVISORS) {
                window.alert(`You can assign at most ${MAX_SUPERVISORS} supervisors to a service point.`);
                applySelection(selectElement, previousSelection);
                return;
            }

            supervisorSelections.set(servicePointId, currentSelection);
        }

        function renderServicePoints(businessId) {
            supervisorSelections = captureCurrentServicePointSelections();

            const servicePoints = allServicePoints.filter((sp) => sp.business_id === businessId);
            const businessUsers = allUsers.filter((user) => user.business_id === businessId);

            servicePointsContainer.innerHTML = '';

            if (!servicePoints.length) {
                servicePointsPlaceholder.textContent = 'No service points found for this business.';
                servicePointsPlaceholder.classList.remove('hidden');
                servicePointsSection.classList.add('hidden');
                return;
            }

            servicePointsPlaceholder.classList.add('hidden');
            servicePointsSection.classList.remove('hidden');

            servicePoints.forEach((servicePoint) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'border border-gray-200 rounded-md p-4';
                wrapper.dataset.servicePointId = servicePoint.id;

                const selectedSet = supervisorSelections.get(servicePoint.id)
                    || initialSupervisorMap.get(servicePoint.id)
                    || new Set();

                wrapper.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">${servicePoint.name || 'Unnamed Service Point'}</h4>
                            ${servicePoint.description ? `<p class="text-xs text-gray-500">${servicePoint.description}</p>` : ''}
                        </div>
                        <span class="text-xs text-gray-400">Pick 1-4</span>
                    </div>
                    <select multiple size="5" name="service_point_supervisors[${servicePoint.id}][]"
                        class="mt-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></select>
                `;

                const selectElement = wrapper.querySelector('select');
                businessUsers.forEach((user) => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.email})`;
                    if (selectedSet.has(Number(user.id))) {
                        option.selected = true;
                    }
                    selectElement.appendChild(option);
                });

                if (selectedSet.size > MAX_SUPERVISORS) {
                    const trimmed = new Set(Array.from(selectedSet).slice(0, MAX_SUPERVISORS));
                    applySelection(selectElement, trimmed);
                    supervisorSelections.set(servicePoint.id, trimmed);
                } else {
                    supervisorSelections.set(servicePoint.id, selectedSet);
                }

                selectElement.addEventListener('change', () => {
                    enforceSupervisorLimit(selectElement, servicePoint.id);
                });

                servicePointsContainer.appendChild(wrapper);
            });
        }

        function handleBusinessChange() {
            const businessId = Number(businessSelect.value);

            if (!businessId) {
                approverSelect.innerHTML = '<option value="" disabled>Select a business first...</option>';
                approverSelect.disabled = true;
                authorizerSelect.innerHTML = '<option value="" disabled>Select a business first...</option>';
                authorizerSelect.disabled = true;
                defaultSupervisorSelect.innerHTML = '<option value="">Select a business first...</option>';
                defaultSupervisorSelect.disabled = true;
                defaultSupervisorSelect.classList.add('bg-gray-100');
                servicePointsContainer.innerHTML = '';
                servicePointsPlaceholder.textContent = 'Select a business to load service points.';
                servicePointsPlaceholder.classList.remove('hidden');
                servicePointsSection.classList.add('hidden');
                syncRoleExclusion();
                return;
            }

            const businessUsers = allUsers.filter((user) => user.business_id === businessId);
            populateMulti(approverSelect, businessUsers, approverSelection);
            populateMulti(authorizerSelect, businessUsers, authorizerSelection);
            populateSingle(defaultSupervisorSelect, businessUsers, defaultSupervisorSelection);
            syncRoleExclusion();
            renderServicePoints(businessId);
        }

        approverSelect.addEventListener('change', () => {
            const newSelection = getSelectedValues(approverSelect);
            if (newSelection.size > MAX_APPROVERS) {
                window.alert(`Select at most ${MAX_APPROVERS} approvers.`);
                applySelection(approverSelect, approverSelection);
                return;
            }

            const overlap = Array.from(newSelection).filter((value) => authorizerSelection.has(value));
            if (overlap.length > 0) {
                window.alert('Approvers and authorizers must be different people.');
                applySelection(approverSelect, approverSelection);
                return;
            }

            approverSelection = newSelection;
            syncRoleExclusion();
        });

        authorizerSelect.addEventListener('change', () => {
            const newSelection = getSelectedValues(authorizerSelect);
            if (newSelection.size > MAX_AUTHORIZERS) {
                window.alert(`Select at most ${MAX_AUTHORIZERS} authorizers.`);
                applySelection(authorizerSelect, authorizerSelection);
                return;
            }

            const overlap = Array.from(newSelection).filter((value) => approverSelection.has(value));
            if (overlap.length > 0) {
                window.alert('Approvers and authorizers must be different people.');
                applySelection(authorizerSelect, authorizerSelection);
                return;
            }

            authorizerSelection = newSelection;
            syncRoleExclusion();
        });

        defaultSupervisorSelect.addEventListener('change', () => {
            const value = Number(defaultSupervisorSelect.value);
            defaultSupervisorSelection = value ? value : null;
        });

        businessSelect.addEventListener('change', () => {
            approverSelection.clear();
            authorizerSelection.clear();
            supervisorSelections.clear();
            defaultSupervisorSelection = null;
            initialSupervisorMap = new Map();
            handleBusinessChange();
        });

        if (businessSelect.value) {
            handleBusinessChange();
        }
    });
</script>
</x-app-layout>

