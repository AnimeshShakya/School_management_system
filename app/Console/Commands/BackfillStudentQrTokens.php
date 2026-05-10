<?php

namespace App\Console\Commands;

use App\Models\Students;
use Illuminate\Console\Command;

class BackfillStudentQrTokens extends Command
{
    protected $signature = 'students:backfill-qr-tokens';

    protected $description = 'Generate QR tokens for all students that do not have one yet';

    public function handle(): int
    {
        $count = 0;

        Students::whereNull('qr_token')
            ->orWhere('qr_token', '')
            ->chunkById(200, function ($students) use (&$count) {
                foreach ($students as $student) {
                    $student->update(['qr_token' => bin2hex(random_bytes(32))]);
                    $count++;
                }
            });

        $this->info("Backfilled QR tokens for {$count} student(s).");

        return self::SUCCESS;
    }
}
