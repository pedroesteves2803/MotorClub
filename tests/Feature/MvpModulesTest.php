<?php

use App\Models\CarEvent;
use App\Models\MediaPhoto;
use App\Models\Project;
use App\Models\ProjectModification;
use App\Models\ProjectPart;
use App\Models\ProjectPhoto;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use Database\Seeders\MvpDemoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function mvpWorkshop(?User $user = null, array $data = []): Workshop
{
    return Workshop::create($data + ['user_id' => ($user ?? User::factory()->create())->id, 'name' => 'Oficina de testes', 'city' => 'São Paulo', 'state' => 'SP', 'specialties' => 'Revisão preventiva', 'description' => 'Oficina para testes automatizados', 'is_public' => true]);
}
function mvpProject(?Vehicle $vehicle = null, array $data = []): Project
{
    return Project::create($data + mvpProjectData($vehicle ?? Vehicle::factory()->create(['visibility' => 'public'])));
}
function mvpProjectData(Vehicle $vehicle): array
{
    return ['vehicle_id' => $vehicle->id, 'title' => 'Revisão do Golf', 'category' => 'maintenance', 'status' => 'ongoing', 'description' => 'Revisão completa para uso diário', 'parts' => 'Filtro de óleo', 'services' => 'Revisão', 'cost' => '1234.56', 'show_cost' => false, 'is_public' => true, 'workshop_id' => null];
}
function mvpEventData(): array
{
    return ['title' => 'Encontro de testes', 'category' => 'meet', 'starts_at' => now('America/Sao_Paulo')->addWeek()->format('Y-m-d\TH:i'), 'city' => 'Curitiba', 'state' => 'PR', 'venue' => 'Local do encontro', 'organizer' => 'Clube de testes', 'description' => 'Programação do encontro de testes', 'is_public' => true];
}

it('renderiza os catálogos e exige login nas áreas pessoais', function () {
    foreach (['projects.index', 'workshops.index', 'events.index'] as $route) {
        $this->get(route($route))->assertOk();
    }
    foreach (['projects.create', 'workshops.edit', 'quotes.index', 'events.create', 'profiles.edit'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
    }
});
it('cria edita pesquisa e exclui projetos próprios', function () {
    $vehicle = Vehicle::factory()->create(['visibility' => 'public', 'brand' => 'Volkswagen']);
    $this->actingAs($vehicle->user)->get(route('projects.create'))->assertOk();
    $this->post(route('projects.store'), mvpProjectData($vehicle))->assertSessionHasNoErrors();
    $project = Project::sole();
    $this->get(route('projects.edit', $project))->assertOk()->assertSee('Editar projeto');
    $this->put(route('projects.update', $project), array_replace(mvpProjectData($vehicle), ['title' => 'Revisão atualizada']))->assertSessionHasNoErrors();
    $this->get(route('projects.index', ['q' => 'Volkswagen']))->assertSee('Revisão atualizada');
    $this->get(route('projects.index', ['q' => 'Inexistente']))->assertDontSee('Revisão atualizada');
    $this->delete(route('projects.destroy', $project))->assertRedirect();
    expect(Project::count())->toBe(0);
});
it('bloqueia apropriação de veículos e alterações em projetos alheios', function () {
    $project = mvpProject();
    $stranger = User::factory()->create();
    $own = Vehicle::factory()->create(['user_id' => $stranger->id]);
    $this->actingAs($stranger)->post(route('projects.store'), mvpProjectData($project->vehicle))->assertSessionHasErrors('vehicle_id');
    $this->get(route('projects.edit', $project))->assertForbidden();
    $this->put(route('projects.update', $project), mvpProjectData($own))->assertForbidden();
    $this->delete(route('projects.destroy', $project))->assertForbidden();
    $this->get(route('projects.index', ['mine' => 1]))->assertDontSee($project->title);
});
it('protege projetos e custos privados mesmo quando vinculados a uma oficina', function () {
    $project = mvpProject();
    $this->get(route('projects.show', $project))->assertOk()->assertDontSee('1.234,56');
    $this->actingAs($project->vehicle->user)->get(route('projects.show', $project))->assertSee('1.234,56');
    auth()->logout();
    $project->update(['show_cost' => true]);
    $this->get(route('projects.show', $project))->assertSee('1.234,56');
    $project->vehicle->update(['visibility' => 'private']);
    $this->get(route('projects.show', $project))->assertNotFound();
    $this->get(route('projects.index'))->assertDontSee($project->title);
    $project->vehicle->update(['visibility' => 'public']);
    $project->update(['is_public' => false]);
    $this->get(route('projects.show', $project))->assertNotFound();
});
it('monta a revista com capítulos relevantes e oculta histórico privado', function () {
    $project = mvpProject(data: ['title' => 'Venom Black MK7']);
    $project->vehicle->entries()->create(['category' => 'maintenance', 'title' => 'Revisão pública', 'description' => 'Serviço documentado', 'occurred_on' => '2026-01-01', 'is_public' => true]);
    $project->vehicle->entries()->create(['category' => 'maintenance', 'title' => 'Anotação privada', 'description' => 'Não publicar', 'occurred_on' => '2026-01-02', 'is_public' => false]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Revista do projeto')
        ->assertSee('Evolução documentada')
        ->assertSee('Revisão pública')
        ->assertDontSee('Anotação privada');

    $this->actingAs($project->vehicle->user)->get(route('projects.show', $project))
        ->assertSee('Anotação privada')
        ->assertSee('Privado')
        ->assertDontSee('Gerar relatório');
});
it('valida campos de projetos e impede injeção de autorização', function () {
    $vehicle = Vehicle::factory()->create();
    $this->actingAs($vehicle->user);
    $this->post(route('projects.store'), ['vehicle_id' => $vehicle->id])->assertSessionHasErrors(['title', 'category', 'status', 'description', 'show_cost', 'is_public']);
    $this->post(route('projects.store'), mvpProjectData($vehicle) + ['workshop_status' => 'approved'])->assertSessionHasNoErrors();
    expect(Project::sole()->workshop_status)->toBe('pending');
});
it('salva fotos com controle de acesso e remove arquivos ao excluir a conta', function () {
    Storage::fake('local');
    $project = mvpProject();
    $owner = $project->vehicle->user;
    $this->actingAs($owner)->post(route('projects.photo', $project), ['photo' => UploadedFile::fake()->image('foto.jpg')])->assertSessionHasNoErrors();
    $photo = ProjectPhoto::sole();
    Storage::disk('local')->assertExists($photo->path);
    auth()->logout();
    $this->get(route('projects.image', $photo))->assertOk()->assertHeader('Content-Type', 'image/webp');
    $project->update(['is_public' => false]);
    $this->get(route('projects.image', $photo))->assertNotFound();
    $this->actingAs(User::factory()->create())->delete(route('projects.photo.delete', $photo))->assertForbidden();
    $owner->delete();
    Storage::disk('local')->assertMissing($photo->path);
    expect(ProjectPhoto::count())->toBe(0);
});
it('aceita várias fotos do projeto em um único envio', function () {
    Storage::fake('local');
    $project = mvpProject();
    $this->actingAs($project->vehicle->user)->post(route('projects.photo', $project), ['photos' => [UploadedFile::fake()->image('frente.jpg'), UploadedFile::fake()->image('traseira.jpg')]])->assertSessionHasNoErrors();
    expect($project->photos()->count())->toBe(2);
    foreach ($project->photos as $photo) {
        Storage::disk('local')->assertExists($photo->path);
    }
});
it('rejeita fotos inválidas e limita a galeria', function () {
    $project = mvpProject();
    $this->actingAs($project->vehicle->user);
    $this->post(route('projects.photo', $project), ['photo' => UploadedFile::fake()->create('script.txt', 10)])->assertSessionHasErrors('photo');
    for ($i = 0; $i < 12; $i++) {
        $project->photos()->create(['path' => 'teste'.$i.'.webp']);
    }
    $this->post(route('projects.photo', $project), ['photo' => UploadedFile::fake()->image('foto.jpg')])->assertStatus(422);
});
it('separa modificações, peças e fotos por nível com controle de acesso', function () {
    Storage::fake('local');
    $project = mvpProject();
    $owner = $project->vehicle->user;
    $this->actingAs($owner);

    $this->post(route('projects.modifications.store', $project), [
        'title' => 'Rodas forjadas',
        'category' => 'rodas',
        'status' => 'in_progress',
        'description' => 'Conjunto em instalação.',
        'is_public' => 1,
    ])->assertSessionHasNoErrors();
    $modification = ProjectModification::sole();

    $this->post(route('projects.parts.store', [$project, $modification]), [
        'name' => 'Roda dianteira',
        'brand' => 'Autoverse Parts',
        'model' => '18x8',
        'status' => 'installed',
        'description' => 'Foto e especificação da peça instalada.',
        'is_public' => 1,
    ])->assertSessionHasNoErrors();
    $part = ProjectPart::sole();

    $this->post(route('projects.modifications.photos.store', [$project, $modification]), [
        'photos' => [UploadedFile::fake()->image('rodas.jpg')],
        'kind' => 'depois',
        'caption' => 'Resultado instalado',
        'is_public' => 1,
    ])->assertSessionHasNoErrors();
    $modificationPhoto = MediaPhoto::sole();
    Storage::disk('local')->assertExists($modificationPhoto->path);
    $this->get(route('projects.media.image', $modificationPhoto))->assertOk();

    $this->post(route('projects.parts.photos.store', [$project, $modification, $part]), [
        'photos' => [UploadedFile::fake()->image('roda-detalhe.jpg')],
        'kind' => 'detalhe',
        'caption' => 'Detalhe da roda',
        'is_public' => 1,
    ])->assertSessionHasNoErrors();
    expect(MediaPhoto::count())->toBe(2);
    $this->get(route('projects.show', $project))->assertSee('Rodas forjadas')->assertSee('Roda dianteira');
    $this->get(route('projects.modifications.show', [$project, $modification]))->assertOk()->assertSee('Rodas forjadas');
    $this->get(route('projects.parts.show', [$project, $modification, $part]))->assertOk()->assertSee('Roda dianteira');

    $this->actingAs(User::factory()->create())->delete(route('projects.modifications.destroy', [$project, $modification]))->assertForbidden();
});
it('mantém oficinas privadas fora dos catálogos e permite cadastro edição e exclusão', function () {
    $user = User::factory()->create();
    $workshop = mvpWorkshop($user, ['is_public' => false]);
    $this->get(route('workshops.show', $workshop))->assertNotFound();
    $this->get(route('workshops.index'))->assertDontSee($workshop->name);
    $this->actingAs($user)->get(route('workshops.edit'))->assertOk();
    $data = $workshop->only(['name', 'city', 'state', 'specialties', 'description']);
    $data['is_public'] = 1;
    $data['whatsapp'] = '5511999999999';
    $this->post(route('workshops.save'), $data)->assertSessionHasNoErrors();
    expect($user->fresh()->account_type)->toBe('workshop');
    $this->get(route('workshops.show', $workshop))->assertOk()->assertSee('https://wa.me/5511999999999');
    $this->get(route('workshops.index', ['q' => 'São Paulo']))->assertSee($workshop->name);
    $this->post(route('workshops.save'), array_replace($data, ['whatsapp' => 'javascript:alert(1)', 'state' => 'XX']))->assertSessionHasErrors(['whatsapp', 'state']);
    $this->delete(route('workshops.destroy'))->assertRedirect();
    expect(Workshop::count())->toBe(0)->and($user->fresh()->account_type)->toBe('enthusiast');
});
it('somente a oficina autoriza e revoga seu portfólio e edições exigem nova autorização', function () {
    $workshop = mvpWorkshop();
    $project = mvpProject(null, ['workshop_id' => $workshop->id]);
    $this->get(route('workshops.show', $workshop))->assertDontSee($project->title);
    $this->actingAs($project->vehicle->user)->post(route('workshops.association', $project), ['workshop_status' => 'approved'])->assertForbidden();
    $this->actingAs($workshop->user)->post(route('workshops.association', $project), ['workshop_status' => 'approved'])->assertRedirect();
    $this->get(route('workshops.show', $workshop))->assertSee($project->title);
    $this->post(route('workshops.association', $project), ['workshop_status' => 'rejected'])->assertRedirect();
    $this->get(route('workshops.show', $workshop))->assertDontSee($project->title);
    $project->update(['workshop_status' => 'approved']);
    $this->actingAs($project->vehicle->user)->put(route('projects.update', $project), array_replace(mvpProjectData($project->vehicle), ['workshop_id' => $workshop->id]))->assertSessionHasNoErrors();
    expect($project->fresh()->workshop_status)->toBe('pending');
});
it('troca pedidos e respostas privados apenas entre os participantes', function () {
    $workshop = mvpWorkshop();
    $vehicle = Vehicle::factory()->create();
    $user = $vehicle->user;
    $this->actingAs($user)->post(route('quotes.store', $workshop), ['subject' => 'Pedido confidencial', 'message' => 'Preciso revisar os freios do veículo', 'vehicle_id' => $vehicle->id])->assertSessionHasNoErrors();
    $quote = QuoteRequest::sole();
    $this->get(route('quotes.index'))->assertSee('Pedido confidencial');
    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get(route('quotes.index'))->assertDontSee('Pedido confidencial');
    $this->put(route('quotes.update', $quote), ['status' => 'cancelled'])->assertForbidden();
    $this->actingAs($workshop->user)->get(route('quotes.index'))->assertSee('Pedido confidencial');
    $this->put(route('quotes.update', $quote), ['status' => 'answered', 'reply' => 'Podemos avaliar na segunda-feira', 'amount' => '450.50'])->assertSessionHasNoErrors();
    $this->actingAs($user)->get(route('quotes.index'))->assertSee('Podemos avaliar')->assertSee('450,50');
    $this->put(route('quotes.update', $quote), ['status' => 'answered', 'reply' => 'Falsa resposta'])->assertSessionHasErrors('status');
    $this->put(route('quotes.update', $quote), ['status' => 'cancelled'])->assertRedirect();
    $this->actingAs($workshop->user)->put(route('quotes.update', $quote), ['status' => 'answered', 'reply' => 'Resposta tardia'])->assertStatus(422);
});
it('rejeita pedidos com veículos alheios oficinas privadas e mensagens inválidas', function () {
    $workshop = mvpWorkshop();
    $vehicle = Vehicle::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->post(route('quotes.store', $workshop), ['subject' => 'Revisão', 'message' => 'Solicito revisão completa', 'vehicle_id' => $vehicle->id])->assertSessionHasErrors('vehicle_id');
    $this->post(route('quotes.store', $workshop), ['subject' => '', 'message' => 'curta'])->assertSessionHasErrors(['subject', 'message']);
    $workshop->update(['is_public' => false]);
    $this->post(route('quotes.store', $workshop), [])->assertNotFound();
});
it('edita a privacidade do perfil sem publicar email ou carros privados', function () {
    $vehicle = Vehicle::factory()->create();
    $user = $vehicle->user;
    $this->get(route('profiles.show', $user))->assertNotFound();
    $this->actingAs($user)->get(route('profiles.edit'))->assertOk();
    $this->put(route('profiles.update'), ['bio' => 'Gosto de carros clássicos', 'city' => 'Curitiba', 'profile_public' => 1, 'account_type' => 'admin'])->assertSessionHasNoErrors();
    expect($user->fresh()->account_type)->toBe('enthusiast');
    auth()->logout();
    $this->get(route('profiles.show', $user))->assertOk()->assertSee('Gosto de carros clássicos')->assertDontSee($user->email)->assertDontSee($vehicle->model);
});
it('cria altera filtra e exclui eventos com fuso de Brasília', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('events.create'))->assertOk();
    $data = mvpEventData();
    $this->post(route('events.store'), $data)->assertSessionHasNoErrors();
    $event = CarEvent::sole();
    expect($event->starts_at->timezone('America/Sao_Paulo')->format('Y-m-d\TH:i'))->toBe($data['starts_at']);
    $this->get(route('events.edit', $event))->assertOk();
    $this->get(route('events.index', ['state' => 'PR', 'q' => 'Curitiba']))->assertSee($event->title);
    $this->get(route('events.index', ['state' => 'SP']))->assertDontSee($event->title);
    $this->put(route('events.update', $event), array_replace($data, ['title' => 'Encontro atualizado']))->assertSessionHasNoErrors();
    $this->get(route('events.show', $event))->assertSee('Encontro atualizado');
    $this->delete(route('events.destroy', $event))->assertRedirect();
    expect(CarEvent::count())->toBe(0);
});
it('protege eventos alheios privados e rejeita datas passadas', function () {
    $owner = User::factory()->create();
    $event = CarEvent::create(mvpEventData() + ['user_id' => $owner->id]);
    $event->update(['is_public' => false]);
    $this->get(route('events.show', $event))->assertNotFound();
    $this->actingAs(User::factory()->create())->get(route('events.edit', $event))->assertForbidden();
    $this->put(route('events.update', $event), mvpEventData())->assertForbidden();
    $this->delete(route('events.destroy', $event))->assertForbidden();
    $this->get(route('events.index', ['mine' => 1]))->assertDontSee($event->title);
    $this->post(route('events.store'), array_replace(mvpEventData(), ['starts_at' => '2020-01-01T10:00']))->assertSessionHasErrors('starts_at');
    $event->update(['is_public' => true, 'starts_at' => now()->subDay()]);
    $this->get(route('events.index'))->assertDontSee($event->title);
});
it('preenche a demonstração de forma repetível preservando dados reais', function () {
    $real = User::factory()->create();
    $before = $real->fresh()->getAttributes();
    $this->seed(MvpDemoSeeder::class);
    $this->seed(MvpDemoSeeder::class);
    expect(Project::count())->toBe(8)->and(Workshop::count())->toBe(3)->and(CarEvent::count())->toBe(3)->and($real->fresh()->getAttributes())->toBe($before);
    foreach (Project::all() as $project) {
        $this->get(route('projects.show', $project))->assertOk()->assertSee('fictício');
    }
    foreach (Workshop::all() as $workshop) {
        $this->get(route('workshops.show', $workshop))->assertOk()->assertSee('fictícia');
    }
    foreach (CarEvent::all() as $event) {
        $this->get(route('events.show', $event))->assertOk()->assertSee('fictício');
    }
    foreach (User::where('is_demo', true)->get() as $person) {
        $this->get(route('profiles.show', $person))->assertOk()->assertDontSee($person->email);
    }
});

it('descobre perfis públicos reais sem incluir contas ou veículos privados', function () {
    $public = User::factory()->create(['name' => 'Pessoa pública']);
    $public->forceFill(['profile_public' => true])->save();
    $private = User::factory()->create(['name' => 'Pessoa reservada']);
    Vehicle::factory()->create(['user_id' => $public->id, 'visibility' => 'public', 'model' => 'Modelo publicado']);
    Vehicle::factory()->create(['user_id' => $public->id, 'visibility' => 'private', 'model' => 'Modelo reservado']);
    $this->get(route('community'))->assertSee('Pessoa pública')->assertSee('Modelo publicado')->assertDontSee('Modelo reservado')->assertDontSee('Pessoa reservada')->assertDontSee($public->email);
    $this->get(route('community', ['q' => 'Modelo publicado']))->assertSee('Pessoa pública');
    $this->get(route('community', ['q' => 'Sem resultado']))->assertDontSee('Pessoa pública');
});
it('não armazena respostas autenticadas em cache compartilhado', function () {
    $this->actingAs(User::factory()->create())->get(route('quotes.index'))->assertHeader('Cache-Control', 'no-store, private');
});
it('explica a restrição do Google na prévia de rede local', function () {
    $this->app['env'] = 'local';
    config(['services.google.client_id' => 'test', 'services.google.client_secret' => 'test', 'services.google.redirect' => 'http://127.0.0.1:8015/auth/google/callback']);
    $this->get('http://192.168.1.126:8016/login')->assertOk()->assertSee('prévia na rede local')->assertDontSee('Continuar com Google');
    $this->get('http://192.168.1.126:8016/auth/google')->assertRedirect()->assertSessionHasErrors('google');
});

it('apresenta erros e validação em português', function () {
    $this->get('/pagina-inexistente')->assertNotFound()->assertSee('Não encontramos esta página');
    $this->actingAs(User::factory()->create())->post(route('events.store'), [])->assertSessionHasErrors(['title' => 'Preencha o campo título.', 'description' => 'Preencha o campo descrição.']);
});
