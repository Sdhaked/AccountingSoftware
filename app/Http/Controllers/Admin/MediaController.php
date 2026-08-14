<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function destroy(Request $request, string $target, ?int $id = null): JsonResponse
    {
        $transactionActive = false;

        try {
            [$record, $field, $deleteRecord, $label] = $this->resolveTarget($request, $target, $id);

            if (! $record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media record not found or access denied.',
                ], 404);
            }

            $path = $record->getAttribute($field);

            if (! $path) {
                return response()->json([
                    'success' => true,
                    'message' => $label.' is already removed.',
                ]);
            }

            DB::beginTransaction();
            $transactionActive = true;

            if ($deleteRecord) {
                method_exists($record, 'forceDelete')
                    ? $record->forceDelete()
                    : $record->delete();
            } else {
                $record->setAttribute($field, null);
                $record->save();
            }

            if (Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
                throw new \RuntimeException($label.' file could not be deleted.');
            }

            DB::commit();
            $transactionActive = false;

            return response()->json([
                'success' => true,
                'message' => $label.' deleted successfully.',
                'record_deleted' => $deleteRecord,
            ]);
        } catch (\Throwable $e) {
            if ($transactionActive) {
                DB::rollBack();
            }

            Log::error('Admin media deletion failed', [
                'target' => $target,
                'record_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media.',
            ], 500);
        }
    }

    /**
     * @return array{0: Model|null, 1: string, 2: bool, 3: string}
     */
    private function resolveTarget(Request $request, string $target, ?int $id): array
    {
        return match ($target) {
            'profile-picture' => [$request->user(), 'profile_picture', false, 'Profile picture'],
            default => [null, '', false, 'Media'],
        };
    }
}
