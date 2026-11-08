<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\Leave;
use App\Models\ReimbursementClaim;
use Illuminate\Support\Facades\Storage;

/**
 * Serves sensitive uploads (receipts, leave attachments, KYC documents)
 * only to the file owner or an admin. These files live on the private disk
 * and are never reachable through a public /storage/... URL.
 */
class SecureFileController extends Controller
{
    public function reimbursementReceipt($id)
    {
        $claim = ReimbursementClaim::findOrFail($id);
        $this->authorizeOwnerOrAdmin($claim->personal_information_id);

        return $this->serve($claim->receipt_path);
    }

    public function leaveAttachment($id)
    {
        $leave = Leave::findOrFail($id);
        $this->authorizeOwnerOrAdmin($leave->personal_information_id);

        return $this->serve($leave->attachment);
    }

    public function employeeDocument($id, $type)
    {
        abort_unless(in_array($type, ['resume', 'aadhaar_card', 'pan_card'], true), 404);

        $document = EmployeeDocument::where('personal_information_id', $id)->firstOrFail();
        $this->authorizeOwnerOrAdmin($document->personal_information_id);

        return $this->serve($document->{$type});
    }

    private function authorizeOwnerOrAdmin($ownerPersonalInformationId): void
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && (int) $ownerPersonalInformationId !== (int) $user->personal_information_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function serve(?string $path)
    {
        abort_if(empty($path) || str_contains($path, '..'), 404);

        // Private disk first; the public disk is only a fallback for files uploaded
        // before the move (run `php artisan files:secure` to migrate them).
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path, null, [
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'private, max-age=0, no-store',
                ]);
            }
        }

        abort(404);
    }
}
