<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\GstrTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // ← needed for the GSTIN-uniqueness rule below

class ClientController extends Controller
{
    private $stateNames = [
        '01' => 'Jammu & Kashmir',    '02' => 'Himachal Pradesh',
        '03' => 'Punjab',             '04' => 'Chandigarh',
        '05' => 'Uttarakhand',        '06' => 'Haryana',
        '07' => 'Delhi',              '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',      '10' => 'Bihar',
        '11' => 'Sikkim',             '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',           '14' => 'Manipur',
        '15' => 'Mizoram',            '16' => 'Tripura',
        '17' => 'Meghalaya',          '18' => 'Assam',
        '19' => 'West Bengal',        '20' => 'Jharkhand',
        '21' => 'Odisha',             '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',     '24' => 'Gujarat',
        '26' => 'Dadra & Nagar Haveli and Daman & Diu',
        '27' => 'Maharashtra',        '29' => 'Karnataka',
        '30' => 'Goa',                '31' => 'Lakshadweep',
        '32' => 'Kerala',             '33' => 'Tamil Nadu',
        '34' => 'Puducherry',         '35' => 'Andaman & Nicobar Islands',
        '36' => 'Telangana',          '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query  = Client::where('user_id', Auth::id());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('gstin',     'like', "%{$search}%")
                  ->orWhere('state_code','like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->paginate(10);

        // Batch count per client — directly from gstr_transactions
        $clientIds   = $clients->pluck('id')->toArray();
        $batchCounts = GstrTransaction::where('user_id', Auth::id())
                           ->whereIn('client_id', $clientIds)
                           ->where('status', 'processed')
                           ->selectRaw('client_id, COUNT(DISTINCT batch_id) as batch_count')
                           ->groupBy('client_id')
                           ->pluck('batch_count', 'client_id');

        foreach ($clients as $client) {
            $client->batch_count = $batchCounts[$client->id] ?? 0;
        }

        // Previous batches — from gstr_transactions grouped by batch_id for the current user
        $batches = GstrTransaction::where('user_id', Auth::id())
            ->where('status', 'processed')
            ->selectRaw('
                batch_id,
                client_id,
                period,
                GROUP_CONCAT(DISTINCT platform) as platforms,
                COUNT(*) as transaction_count,
                SUM(taxable_value) as taxable_value,
                SUM(igst_amount + cgst_amount + sgst_amount) as total_tax,
                MAX(created_at) as created_at
            ')
            ->groupBy('batch_id', 'client_id', 'period')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Attach client name to each batch for the current user
        $clientMap = Client::where('user_id', Auth::id())
            ->whereIn('id', $batches->pluck('client_id'))
            ->pluck('name', 'id');
        foreach ($batches as $batch) {
            $batch->client_name = $clientMap[$batch->client_id] ?? 'Unknown';
        }

        $selectedClientId = session('gstr_selected_client.id')
                         ?? session('selected_client.id');

        // ← SUBSCRIPTION: current user + billing/invoice history for the
        // Profile/Subscription section on client.blade.php
        $user = Auth::user();
        $billingHistory = $user->payments()
            ->with(['plan', 'invoice'])
            ->where('status', 'paid')
            ->latest()
            ->get();

        return view('client', compact('clients', 'batches', 'selectedClientId', 'user', 'billingHistory'));
    }

    public function history(Client $client)
    {
        // Eager load batches with transactions to get all necessary data
        $batches = \App\Models\GstrBatch::where('user_id', Auth::id())
            ->where('client_id', $client->id)
            ->orderBy('period', 'desc')
            ->get();

        return view('client-history', compact('client', 'batches'));
    }

    public function destroyBatch($batchId)
    {
        $batch = \App\Models\GstrBatch::where('user_id', Auth::id())->where('batch_id', $batchId)->firstOrFail();

        // Delete associated transactions
        GstrTransaction::where('user_id', Auth::id())->where('batch_id', $batchId)->delete();

        // Delete the batch record
        $batch->delete();

        return redirect()->back()->with('success', 'Batch deleted successfully!');
    }

    public function store(Request $request)
    {
        // ← SUBSCRIPTION: GST-number limit check
        // 5 GSTINs on free trial, 50 on any paid plan (UI shows "Unlimited" for paid).
        // Yeh check validation se PEHLE hai taaki limit-cross hone par form
        // submit hi na ho aur user ko turant clear message mile.
        $user = Auth::user();
        $currentCount = Client::where('user_id', $user->id)->count();
        $limit = $user->currentGstinLimit();

        if ($currentCount >= $limit) {
            $message = $user->hasActiveSubscription()
                ? "You've reached your plan's GST number limit."
                : "Free trial allows up to {$limit} GST numbers. Upgrade your plan to add more.";

            return redirect()->route('gstr.clients.index')->with('error', $message);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            // GSTIN must be unique PER USER. Without this, the same GSTIN
            // could be added as a second/third client, which then splits
            // one seller's data across multiple client records and confuses
            // the upload/summary flow (which is keyed off client + GSTIN).
            // ->where(...) scopes the uniqueness check to the logged-in
            // user only, so two different users on this platform can still
            // each have a client with that GSTIN — only the SAME user is
            // blocked from adding it twice.
            'gstin'      => [
                'required', 'string', 'size:15',
                Rule::unique('clients', 'gstin')->where(fn($q) => $q->where('user_id', Auth::id())),
            ],
            'state_code' => 'required|string|max:5',
            'filing_frequency' => 'required|in:monthly,quarterly',
        ], [
            'gstin.unique' => 'This GSTIN already exists. Please choose the existing client.',
        ]);

        $validated['state_name'] = $this->stateNames[$validated['state_code']] ?? $validated['state_code'];
        $validated['user_id']    = Auth::id();

        Client::create($validated);

        return redirect()->route('gstr.clients.index')
                         ->with('success', 'Client added successfully!');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            // Same uniqueness rule as store(), but ->ignore() excludes
            // THIS client's own row so editing a client without changing
            // its GSTIN doesn't trip the uniqueness check against itself.
            'gstin'      => [
                'required', 'string', 'size:15',
                Rule::unique('clients', 'gstin')
                    ->where(fn($q) => $q->where('user_id', Auth::id()))
                    ->ignore($client->id),
            ],
            'state_code' => 'required|string|max:5',
            'filing_frequency' => 'required|in:monthly,quarterly',
        ], [
            'gstin.unique' => 'Is GSTIN se ek dusra client pehle se add hai.',
        ]);

        $validated['state_name'] = $this->stateNames[$validated['state_code']] ?? $validated['state_code'];

        $client->update($validated);

        // Agar yahi client abhi session mein "selected" hai, to session ka
        // cached data bhi turant refresh karo — warna upload/summary page
        // purana (stale) filing_frequency dikhata rahega jab tak user
        // dobara "Select" button na dabaye.
        $currentlySelectedId = session('gstr_selected_client.id') ?? session('selected_client.id');
        if ($currentlySelectedId == $client->id) {
            session([
                'selected_client'      => $client->fresh()->toArray(),
                'gstr_selected_client' => [
                    'id'               => $client->id,
                    'name'             => $client->name,
                    'gstin'            => $client->gstin,
                    'state'            => $client->state_code,
                    'state_code'       => $client->state_code,
                    'filing_frequency' => $client->filing_frequency,
                    'state_name'       => $client->state_name,
                ],
            ]);
        }

        return redirect()->route('gstr.clients.index')
                         ->with('success', 'Client updated!');
    }

    public function destroy(Client $client)
    {
        if (session('selected_client_id') == $client->id) {
            session()->forget(['selected_client_id', 'selected_client', 'gstr_selected_client']);
        }

        $client->delete();

        return redirect()->route('gstr.clients.index')
                         ->with('success', 'Client deleted!');
    }

    public function select(Client $client)
    {
        // Client ki current filing_frequency ke hisaab se hi latest batch
        // dhoondo. Pehle yeh check nahi hota tha, isliye monthly client
        // select karne par bhi kabhi-kabhi uska purana quarterly-era ka
        // batch (ya vice-versa) uth jaata tha aur summary page galat
        // frequency ka data dikhata tha.
        //
        // Quarterly batches mein filing_period hamesha set hota hai
        // (e.g. "Q1/2025-26"), monthly batches mein woh hamesha NULL
        // hota hai (dekho GstrUploadController::processTransactions()) —
        // isliye yahi column current frequency se match karne ke liye
        // sabse reliable filter hai.
        $frequency = $client->filing_frequency ?? 'monthly';

        $latestBatchQuery = GstrTransaction::where('user_id', Auth::id())
            ->where('client_id', $client->id)
            ->where('status', 'processed');

        if ($frequency === 'quarterly') {
            $latestBatchQuery->whereNotNull('filing_period');
        } else {
            $latestBatchQuery->whereNull('filing_period');
        }

        $latestBatch = $latestBatchQuery
            ->selectRaw('batch_id, period, filing_period, COUNT(*) as tx_count, MAX(created_at) as created_at')
            ->groupBy('batch_id', 'period', 'filing_period')
            ->orderByDesc('created_at')
            ->first();

        session([
            'selected_client_id'     => $client->id,
            'selected_client'        => $client->toArray(),
            'gstr_selected_client'   => [
                'id'         => $client->id,
                'name'       => $client->name,
                'gstin'      => $client->gstin,
                'state'      => $client->state_code,
                'state_code' => $client->state_code,
                'filing_frequency' => $client->filing_frequency,
                'state_name' => $client->state_name,
            ],
        ]);

        // Agar isi frequency ka purana data hai to session mein restore karo
        // aur summary pe bhejo — warna Upload page pe bhejo (naya data daalne ke liye)
        if ($latestBatch) {
            session([
                'gstr_batch_id'           => $latestBatch->batch_id,
                'gstr_period'             => $latestBatch->period,
                'gstr_filing_period'      => $latestBatch->filing_period,
                'gstr_transaction_count'  => $latestBatch->tx_count,
            ]);

            return redirect()->route('gstr.summary')
                             ->with('success', "'{$client->name}' selected — showing latest {$frequency} data.");
        }

        // Purana data isi frequency ka nahi mila — stale session keys clear karo
        // taaki summary page galti se pichli frequency ka batch na dikha de
        session()->forget(['gstr_batch_id', 'gstr_period', 'gstr_filing_period', 'gstr_transaction_count']);

        return redirect()->route('gstr.upload.index')
                         ->with('success', "'{$client->name}' selected. Please import data.");
    }
}