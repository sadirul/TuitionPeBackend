<?php

namespace App\Jobs;

use App\Helpers\SmsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // ✅ include Dispatchable here

    protected string $phone;
    protected string $messageId;
    protected string $variablesValues;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $messageId, string $variablesValues = '')
    {
        $this->phone = $phone;
        $this->messageId = $messageId;
        $this->variablesValues = $variablesValues;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $sent = SmsHelper::sendSms(
                phone: $this->phone,
                messageId: $this->messageId,
                variablesValues: $this->variablesValues
            );

            if ($sent) {
                Log::info("✅ SMS sent successfully to {$this->phone}");
            } else {
                Log::warning("❌ Failed to send SMS to {$this->phone}");
            }
        } catch (\Throwable $e) {
            Log::error("Error in SmsJob: " . $e->getMessage());
        }
    }
}
