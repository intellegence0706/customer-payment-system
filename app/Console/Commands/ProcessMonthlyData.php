<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ProcessMonthlyData extends Command
{
    protected $signature = 'monthly:process {--month=} {--year=}';
    protected $description = 'Process monthly payment data automatically';

    public function handle()
    {
        $month = $this->option('month') ?: now()->month;
        $year = $this->option('year') ?: now()->year;

        $this->info("Processing monthly data for {$month}/{$year}");

        // Check for uploaded files in the monthly data directory
        $files = Storage::files("monthly-data/{$year}/{$month}");
        
        if (empty($files)) {
            $this->warn("No files found for processing in monthly-data/{$year}/{$month}");
            return;
        }

        $processed = 0;
        $errors = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'csv') {
                continue;
            }

            $this->info("Processing file: {$file}");
            
            try {
                $result = $this->processFile($file, $month, $year);
                $processed += $result['processed'];
                $errors = array_merge($errors, $result['errors']);
            } catch (\Exception $e) {
                $errors[] = "Error processing file {$file}: " . $e->getMessage();
            }
        }

        $this->info("Processing completed. Processed: {$processed} payments");
        
        if (!empty($errors)) {
            $this->warn("Errors encountered:");
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        // Generate summary report
        $this->generateSummaryReport($month, $year, $processed, $errors);
    }

    private function processFile($filePath, $month, $year)
    {
        $handle = fopen(storage_path('app/' . $filePath), 'r');
        $header = fgetcsv($handle);
        
        $processed = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $customer = Customer::where('customer_number', $data[0])->first();
                
                if (!$customer) {
                    $errors[] = "Customer not found: {$data[0]}";
                    continue;
                }

                // Check if payment already exists
                $existingPayment = Payment::where('customer_id', $customer->id)
                    ->where('payment_month', $month)
                    ->where('payment_year', $year)
                    ->first();

                if ($existingPayment) {
                    // Update existing payment
                    $existingPayment->update([
                        'amount' => $data[1] ?? 0,
                        'payment_date' => Carbon::parse($data[2] ?? now()),
                        'receipt_number' => $data[3] ?? null,
                        'status' => 'completed',
                        'notes' => 'Updated from automated monthly processing',
                    ]);
                } else {
                    // Create new payment
                    Payment::create([
                        'customer_id' => $customer->id,
                        'payment_month' => $month,
                        'payment_year' => $year,
                        'amount' => $data[1] ?? 0,
                        'payment_date' => Carbon::parse($data[2] ?? now()),
                        'receipt_number' => $data[3] ?? null,
                        'status' => 'completed',
                        'notes' => 'Imported from automated monthly processing',
                    ]);
                }

                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Error processing row: " . implode(',', $data) . " - " . $e->getMessage();
            }
        }

        fclose($handle);
        
        // Move processed file to archive
        $archivePath = "monthly-data/archive/{$year}/{$month}/" . basename($filePath);
        Storage::move($filePath, $archivePath);

        return ['processed' => $processed, 'errors' => $errors];
    }

    private function generateSummaryReport($month, $year, $processed, $errors)
    {
        $report = [
            'month' => $month,
            'year' => $year,
            'processed_at' => now(),
            'total_processed' => $processed,
            'total_errors' => count($errors),
            'errors' => $errors,
        ];

        $filename = "monthly_processing_report_{$year}_{$month}_" . date('Y-m-d_H-i-s') . '.json';
        Storage::put("reports/monthly/{$filename}", json_encode($report, JSON_PRETTY_PRINT));

        $this->info("Summary report saved: {$filename}");
    }
}
