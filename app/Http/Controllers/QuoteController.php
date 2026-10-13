<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $quotes = QuoteRequest::with(['user', 'workshop', 'vehicle'])->where(fn ($q) => $q->where('user_id', $request->user()->id)->orWhereHas('workshop', fn ($w) => $w->where('user_id', $request->user()->id)))->latest()->paginate(15);

        return view('quotes.index', compact('quotes'));
    }

    public function store(Request $request, Workshop $workshop)
    {
        abort_unless($workshop->is_public, 404);
        abort_if($workshop->user_id === $request->user()->id, 422, 'Escolha outra oficina.');
        $data = $request->validate(['subject' => 'required|string|max:150', 'message' => 'required|string|min:10|max:5000', 'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('user_id', $request->user()->id)]]);
        QuoteRequest::create($data + ['user_id' => $request->user()->id, 'workshop_id' => $workshop->id]);

        return redirect()->route('quotes.index')->with('status', 'Solicitação registrada na caixa de entrada da oficina.');
    }

    public function update(Request $request, QuoteRequest $quote)
    {
        if ($quote->workshop->user_id === $request->user()->id) {
            abort_if($quote->status === 'cancelled', 422, 'Este pedido foi cancelado.');
            $data = $request->validate(['status' => ['required', Rule::in(['answered', 'declined'])], 'reply' => 'required|string|max:5000', 'amount' => 'nullable|numeric|min:0|max:9999999999.99|decimal:0,2']);
        } else {
            abort_unless($quote->user_id === $request->user()->id, 403);
            $request->validate(['status' => ['required', Rule::in(['cancelled'])]]);
            $data = ['status' => 'cancelled'];
        }
        $quote->update($data);

        return back()->with('status', 'Pedido atualizado.');
    }
}
