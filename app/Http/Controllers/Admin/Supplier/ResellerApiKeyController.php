<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\BaseController;
use App\Models\ResellerApiKey;
use App\Services\ResellerApiService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ResellerApiKeyController extends BaseController
{
    /**
     * Display all reseller API keys.
     */
    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        $search = $request?->get('searchValue');

        $keys = ResellerApiKey::query()
            ->with('user')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderByDesc('id')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('admin-views.reseller.list', compact('keys', 'search'));
    }

    /**
     * Generate a new API key pair for a customer.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:100',
        ]);

        $keyRecord = ResellerApiService::generateKeyPair(
            userId: (int) $request->input('user_id'),
            name: $request->input('name'),
        );

        Toastr::success(translate('api_key_generated_successfully'));

        return redirect()->route('admin.reseller-keys.list')->with([
            'new_key_id' => $keyRecord->id,
            'raw_api_key' => $keyRecord->raw_api_key,
            'raw_api_secret' => $keyRecord->raw_api_secret,
        ]);
    }

    /**
     * Toggle active status of an API key.
     */
    public function toggleStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:reseller_api_keys,id',
        ]);

        $key = ResellerApiKey::findOrFail($request->input('id'));
        $key->update(['is_active' => ! $key->is_active]);

        $status = $key->is_active ? translate('activated') : translate('deactivated');
        Toastr::success(translate('API_key') . ' ' . $status);

        return redirect()->back();
    }

    /**
     * Permanently delete an API key.
     */
    public function delete(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:reseller_api_keys,id',
        ]);

        ResellerApiKey::findOrFail($request->input('id'))->delete();

        Toastr::success(translate('api_key_deleted_successfully'));

        return redirect()->route('admin.reseller-keys.list');
    }

    /**
     * Show the API documentation page.
     */
    public function apiDocs(): View
    {
        return view('admin-views.reseller.api-docs');
    }
}
