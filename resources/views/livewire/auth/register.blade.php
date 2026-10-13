<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
new #[Layout('components.layouts.auth')] class extends Component {}; ?>
<div><a href="{{ route('login') }}">Cadastrar com Google</a></div>
