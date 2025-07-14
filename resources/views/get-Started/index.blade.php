<x-app-layout>
    <div class="py-12" x-data="{ showModal: false }" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Info Box --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Get Started with MarzPay</h2>
                </div>

                <div class="bg-blue-50 dark:bg-gray-700 p-4 rounded-md border border-blue-200 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 mb-6">
                    <p class="font-semibold mb-2">Required Documents Include:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Certificate of Registration/Incorporation</li>
                        <li>Business Profile Document</li>
                        <li>Memorandum of Understanding (MoU)</li>
                        <li>Cancelled Bank Cheque</li>
                        <li>Signed Agreement (view & download below)</li>
                        <li>UMRA Certificate (if applicable)</li>
                        <li>Company Forms (Form 18, 20, etc.)</li>
                        <li>At least 2 Directors’ National IDs</li>
                        <li>Tax Clearance Certificate</li>
                    </ul>
                </div>

                {{-- Agreement Download via Google Docs --}}
                <div class="mb-6">
                    <a href="https://docs.google.com/document/d/1ss20hGD-7GagnULH9hm8ngQWBzI-0SAuGPp1qcqNsug/edit?usp=sharing"
                        target="_blank"
                        class="inline-block bg-green-600 text-white text-sm px-4 py-2 rounded hover:bg-green-700 transition">
                        📄 View and Download Wallet Agreement
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Please sign the agreement and upload it as "Signed Agreement" below.
                    </p>
                </div>

                {{-- Upload Document Form --}}
                <form action="{{ route('business-documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Title</label>
                        <input type="text" name="title" id="title" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="e.g. Certificate of Incorporation">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
                        <textarea name="description" id="description" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Brief info about the document"></textarea>
                    </div>

                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Document</label>
                        <input type="file" name="document_file" id="document_file" required accept=".pdf,.jpg,.jpeg,.png"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-[#011478] text-white rounded hover:bg-[#011478]/90">
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>

            {{-- Uploaded Documents --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Your Uploaded Documents</h3>
                @livewire('documents.list-business-documents')
            </div>

        </div>
    </div>
</x-app-layout>
