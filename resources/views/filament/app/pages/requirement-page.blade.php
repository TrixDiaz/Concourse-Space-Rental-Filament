<x-filament-panels::page>
    <style>
        @media print {

            .no-print,
            .fi-topbar,
            .fi-header {
                display: none !important;
            }

            body {
                padding: 0 !important;
            }

            .fi-main {
                padding: 0 !important;
            }

            .print-small-text {
                font-size: 8px !important;
            }

            .print-compact {
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 5px;
            }
        }
    </style>

    <x-filament::section>
        <x-filament::card>
            <div class="flex justify-between gap-4 no-print">
                <div>
                    <x-filament::button
                        type="button"
                        icon="heroicon-m-printer"
                        size="lg"
                        onclick="window.print()">
                        Print
                    </x-filament::button>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold">RGO FORM 4</p>
                    <p class="text-sm">New Applicant</p>
                </div>
            </div>

            <div class="text-center">
                <p class="text-sm">Republic of the Philippines</p>
                <p class="text-md font-semibold">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <p class="text-sm">Office of the Vice President for Finance</p>
                <p class="text-md font-semibold">RESOURCE GENERATION OFFICE</p>
            </div>

            <div class="text-center">
                <p class="text-md font-medium">CONCESSION'S APPLICATION FORM</p>
            </div>

            <div class="flex justify-center gap-8">
                <label class="flex items-center">
                    <x-filament::input.checkbox wire:model="foodStall" disabled />
                    <span class="p-2">Food Stall</span>
                </label>
                <label class="flex items-center">
                    <x-filament::input.checkbox wire:model="nonFoodStall" disabled />
                    <span class="p-2">Non-food Stall</span>
                </label>
            </div>

            <div class="print-compact">
                <h3 class="text-md font-medium mb-2 text-center">PROFILE</h3>
                <div class="print-grid">
                    <label for="businessName">Business Name</label>
                    <x-filament::input.wrapper label="Business Name">
                        <x-filament::input type="text" wire:model="businessName" disabled />
                    </x-filament::input.wrapper>
                    <label for="ownerName">Owner's Name</label>
                    <x-filament::input.wrapper label="Owner's Name">
                        <x-filament::input type="text" wire:model="ownerName" disabled />
                    </x-filament::input.wrapper>
                    <label for="permanentAddress">Permanent Address</label>
                    <x-filament::input.wrapper label="Permanent Address">
                        <x-filament::input type="text" wire:model="permanentAddress" disabled />
                    </x-filament::input.wrapper>
                    <label for="currentAddress">Current Address</label>
                    <x-filament::input.wrapper label="Current Address">
                        <x-filament::input type="text" wire:model="currentAddress" disabled />
                    </x-filament::input.wrapper>
                    <label for="contactNumber">Contact Number</label>
                    <x-filament::input.wrapper label="Contact Number">
                        <x-filament::input type="tel" wire:model="contactNumber" disabled />
                    </x-filament::input.wrapper>
                    <label for="emailAddress">Email Address</label>
                    <x-filament::input.wrapper label="Email Address">
                        <x-filament::input type="email" wire:model="emailAddress" disabled />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="mt-1 print-compact">
                <h3 class="text-sm font-semibold mb-2">REQUIREMENTS</h3>
                <x-filament::grid columns="1">
                    @foreach([
                    'Letter of Intent',
                    'DTI Certificate/SEC Registration',
                    'Business Permit',
                    'Barangay Clearance',
                    'Sanitary Permit (for food stall)',
                    'Health Certification of Personnel',
                    'Proof of Billing (in the name of applicant)',
                    'Photocopy of Government issued ID',
                    'Organization Chart with Photo',
                    'List of Menu (for food stall)',
                    'List of Office Supplies for sale (non-food)',
                    'Services Offered (non-food)',
                    'Business Application Fee (PHP 150.00)'
                    ] as $requirement)
                    <label class="flex items-center gap-2">
                        <x-filament::input.checkbox
                            wire:model="selectedRequirements"
                            :value="$requirement"
                            disabled />
                        <span class="m-2 text-sm">{{ $requirement }}</span>
                    </label>
                    @endforeach
                </x-filament::grid>
            </div>

            <div class="flex justify-center items-center gap-8 print-small-text mt-2">
                <div>
                    <p class=" text-sm">Assessed by:</p>
                <div class="mt-2 border-t border-gray-300 p-1 w-48">
                    <p class="text-xs text-center">(Signature over printed name of Staff)</p>
                </div>
            </div>
            <div>
                <p class="text-sm">Endorsed by:</p>
                <div class="mt-2 border-t border-gray-300 p-1 w-48">
                    <p class="text-xs text-center">(Signature over printed name of Director)</p>
                </div>
            </div>
            </div>

        </x-filament::card>
    </x-filament::section>
</x-filament-panels::page>