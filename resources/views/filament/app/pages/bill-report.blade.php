<x-filament-panels::page>
    <div class="flex flex-col gap-8">
        <div id="fi-report" class="bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-900 rounded-lg shadow-md">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Payments Report</h2>
                <p class="text-gray-600 dark:text-gray-400">This report shows payments in the system</p>
            </div>

            <div class="p-6">
                {{ $this->table }}
            </div>

            <div class="p-6">
                <table class="w-full mt-8 border-t-2 border-b-2 border-yellow-500">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300">Payment Method</th>
                            <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300">Total Transactions</th>
                            <th class="py-2 px-4 text-left font-semibold text-gray-700 dark:text-gray-300">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getPaymentSummary() as $summary)
                        <tr>
                            <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $summary['method'] }}</td>
                            <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $summary['transactions'] }}</td>
                            <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ number_format($summary['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6">
                <p class="text-2xl font-bold text-yellow-500">Coms</p>
                <p class="text-gray-600 dark:text-gray-400">Generated on: {{ now()->format('F d, Y') }}</p>
            </div>
        </div>
    </div>

    <style>
        @import '../../vendor/filament/filament/resources/css/theme.css';

        #fi-report .fi-ta-text {
            padding-top: 0px !important;
            padding-bottom: 0px !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
        }

        @media print {
            body {
                background-color: white !important;
            }

            .filament-main-content {
                padding: 0 !important;
            }

            .filament-header,
            .filament-sidebar,
            .filament-topbar {
                display: none !important;
            }

            #fi-report {
                box-shadow: none !important;
                border: none !important;
            }

            .text-gray-600,
            .text-gray-700,
            .text-gray-900 {
                color: black !important;
            }

            .border-yellow-500 {
                border-color: #000 !important;
            }

            .text-yellow-500 {
                color: #000 !important;
            }

            /* Hide all non-essential table elements */
            .filament-tables-pagination,
            .filament-tables-header-cell button,
            .filament-tables-search-input,
            .filament-tables-filter-indicator,
            .filament-tables-filter-trigger,
            .filament-tables-column-toggling-trigger,
            .filament-tables-actions-container,
            .filament-tables-footer,
            .filament-tables-header {
                display: none !important;
            }

            /* Ensure all rows are displayed */
            .filament-tables-table tbody tr {
                display: table-row !important;
            }

            /* Remove any fixed height on the table body */
            .filament-tables-table-container {
                max-height: none !important;
                overflow: visible !important;
            }

            /* Ensure table takes full width */
            .filament-tables-table {
                width: 100% !important;
            }

            /* Adjust cell padding for print */
            .filament-tables-table th,
            .filament-tables-table td {
                padding: 8px !important;
            }
        }

        @page {
            margin: 2cm;
        }
    </style>

    @push('scripts')
    <script>
        function printReport() {
            // Store the original overflow
            const originalOverflow = document.body.style.overflow;

            // Hide scrollbars
            document.body.style.overflow = 'hidden';

            // Force all rows to be visible
            const tableRows = document.querySelectorAll('.filament-tables-table tbody tr');
            tableRows.forEach(row => {
                row.style.display = 'table-row';
            });

            // Hide header and footer
            const header = document.querySelector('.filament-tables-header');
            const footer = document.querySelector('.filament-tables-footer');
            if (header) header.style.display = 'none';
            if (footer) footer.style.display = 'none';

            // Print the page
            window.print();

            // Restore the original overflow
            document.body.style.overflow = originalOverflow;

            // Restore original row display
            tableRows.forEach(row => {
                row.style.display = '';
            });

            // Restore header and footer
            if (header) header.style.display = '';
            if (footer) footer.style.display = '';
        }

        function exportToExcel() {
            let table2excel = new Table2Excel();
            let element = document.getElementById("fi-report");
            table2excel.export(element, "report.xlsx");
        }

        // Attach the functions to the window object so they can be called from Filament actions
        window.printReport = printReport;
        window.exportToExcel = exportToExcel;
    </script>
    @endpush
</x-filament-panels::page>
