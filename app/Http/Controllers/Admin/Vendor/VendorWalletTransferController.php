<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\WalletTransfer;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VendorWalletTransferController extends BaseController
{
    /**
     * Show the transfer form.
     */
    public function index(?Request $request = null, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        $vendors = Seller::with('shop', 'wallet')
            ->approved()
            ->orderBy('f_name')
            ->get();

        $transfers = WalletTransfer::where('from_user_type', 'admin')
            ->where('to_user_type', 'vendor')
            ->with('toUser.shop')
            ->latest()
            ->paginate(20);

        return view('admin-views.vendor.wallet-transfer', compact('vendors', 'transfers'));
    }

    /**
     * Transfer balance from admin to vendor wallet.
     */
    public function transfer(Request $request): RedirectResponse
    {
        $request->validate([
            'vendor_id' => 'required|exists:sellers,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
        ]);

        $vendorId = $request->vendor_id;
        $amount = $request->amount;

        Seller::findOrFail($vendorId);

        DB::beginTransaction();
        try {
            $sellerWallet = SellerWallet::firstOrCreate(
                ['seller_id' => $vendorId],
                [
                    'total_earning' => 0,
                    'pending_balance' => 0,
                    'withdrawn' => 0,
                    'commission_given' => 0,
                    'pending_withdraw' => 0,
                    'delivery_charge_earned' => 0,
                    'collected_cash' => 0,
                    'total_tax_collected' => 0,
                ]
            );

            $sellerWallet->increment('total_earning', $amount);

            WalletTransfer::create([
                'from_user_type' => 'admin',
                'from_user_id' => auth('admin')->id(),
                'to_user_type' => 'vendor',
                'to_user_id' => $vendorId,
                'amount' => $amount,
                'reference' => $request->reference,
            ]);

            DB::commit();

            ToastMagic::success(translate('balance_transferred_successfully'));

            return redirect()->route('admin.vendors.wallet-transfer.index');
        } catch (\Exception $e) {
            DB::rollBack();
            ToastMagic::error(translate('transfer_failed').': '.$e->getMessage());

            return redirect()->back()->withInput();
        }
    }
}
