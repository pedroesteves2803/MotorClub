<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function create(Request $request)
    {
        return view('feedback.create', [
            'email' => $request->user()?->email,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys($this->categories()))],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'email' => ['nullable', 'email', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'website' => ['nullable', 'max:0'],
        ], ['website.max' => 'Não foi possível enviar este formulário.']);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('feedback', config('filesystems.private_disk'))
            : null;

        Feedback::create([
            'user_id' => $request->user()?->id,
            'category' => $data['category'],
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $path,
            'email' => $request->user()?->email ?: ($data['email'] ?? null),
            'status' => 'received',
            'technical_context' => [
                'route' => $request->header('Referer') ? parse_url($request->header('Referer'), PHP_URL_PATH) : null,
                'user_agent_hash' => hash_hmac('sha256', (string) $request->userAgent(), (string) config('app.key')),
            ],
        ]);

        return redirect()->route('feedback.create')->with('status', 'Obrigado. Seu feedback foi recebido pela equipe.');
    }

    public function categories(): array
    {
        return [
            'suggestion' => 'Sugestão de melhoria',
            'feature' => 'Nova funcionalidade',
            'bug' => 'Reportar um bug',
            'usability' => 'Problema de usabilidade',
            'praise' => 'Elogio',
            'other' => 'Outro assunto',
        ];
    }
}
