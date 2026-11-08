<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureUploads extends Command
{
    protected $signature = 'files:secure {--dry-run : Only list the files that would be moved}';

    protected $description = 'Move sensitive uploads (KYC documents, receipts, leave attachments) from the public disk to the private disk';

    /** Folders that must never be publicly reachable. Employee photos stay public (they are shown as avatars). */
    private array $folders = ['documents', 'reimbursements', 'leave_attachments'];

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;

        foreach ($this->folders as $folder) {
            foreach ($public->allFiles($folder) as $file) {
                if (basename($file) === '.gitignore') {
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("would move: {$file}");
                    $moved++;
                    continue;
                }

                $stream = $public->readStream($file);
                if (!$stream) {
                    $this->warn("could not read: {$file}");
                    continue;
                }

                $private->writeStream($file, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($private->exists($file)) {
                    $public->delete($file);
                    $moved++;
                } else {
                    $this->warn("copy failed, original kept: {$file}");
                }
            }
        }

        $this->info(($this->option('dry-run') ? 'Files to move: ' : 'Files moved to private storage: ') . $moved);

        return self::SUCCESS;
    }
}
