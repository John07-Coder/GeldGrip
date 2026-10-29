<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ExpenseOverview;
use App\Services\FinancialOverview;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SpendingAndSimulatorTest extends TestCase
{
    use RefreshDatabase;

    private function expense(array $overrides = []): array
    {
        return array_merge(['name' => 'Albert Heijn', 'amount' => '24.50', 'category' => 'groceries',
            'expense_date' => '2026-10-28', 'description' => 'Boodschappen'], $overrides);
    }

    public function test_expense_crud_normalizes_amounts_and_redirects_to_the_expense_month(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('spending.store'), $this->expense(['amount' => '24,50', 'user_id' => 999, 'month' => '2026-09']))
            ->assertSessionHasNoErrors()->assertRedirect(route('spending.index', ['month' => '2026-10']));
        $expense = $user->expenses()->firstOrFail();
        $this->assertSame('24.50', $expense->amount);
        $this->assertSame($user->id, $expense->user_id);
        $this->get(route('spending.edit', $expense))->assertOk()->assertSee('Boodschappen');
        $this->put(route('spending.update', $expense), $this->expense(['amount' => '30.01', 'expense_date' => '2026-11-01', 'return_to' => 'dashboard']))
            ->assertSessionHasNoErrors()->assertRedirect(route('dashboard', ['month' => '2026-11']));
        $this->assertSame('30.01', $expense->fresh()->amount);
        $this->delete(route('spending.destroy', [$expense, 'month' => '2026-11']))->assertRedirect(route('spending.index', ['month' => '2026-11']));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_month_boundaries_statistics_and_six_month_trend_are_exact(): void
    {
        $user = User::factory()->create();
        foreach ([['expense_date' => '2026-04-30', 'amount' => '999.00'],
            ['expense_date' => '2026-05-01', 'amount' => '10.00'],
            ['expense_date' => '2026-09-30', 'amount' => '20.00'],
            ['expense_date' => '2026-10-01', 'amount' => '0.01', 'name' => 'Eerste dag'],
            ['expense_date' => '2026-10-31', 'amount' => '0.02', 'name' => 'Laatste dag', 'category' => 'health'],
            ['expense_date' => '2026-11-01', 'amount' => '500.00', 'name' => 'Volgende maand']] as $data) {
            $user->expenses()->create($this->expense($data));
        }
        User::factory()->create()->expenses()->create($this->expense(['amount' => '999.00']));
        $overview = app(ExpenseOverview::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'));
        $this->assertSame(3, $overview['total']);
        $this->assertSame(2, $overview['count']);
        $this->assertSame(2, $overview['average']); // Half a cent rounds up once.
        $this->assertSame('Gezondheid', $overview['largestCategory']['label']);
        $this->assertSame([1000, 0, 0, 0, 2000, 3], $overview['trend']->pluck('cents')->all());
        $this->actingAs($user)->get(route('spending.index', ['month' => '2026-10']))->assertOk()
            ->assertSee('Eerste dag')->assertSee('Laatste dag')->assertDontSee('Volgende maand')->assertViewHas('total', 3);
        $this->get(route('spending.index', ['month' => '2026-11']))->assertViewHas('total', 50000)->assertDontSee('Laatste dag');
    }

    public function test_expenses_are_isolated_for_every_action_and_simulator(): void
    {
        $owner = User::factory()->create();
        $expense = $owner->expenses()->create($this->expense(['name' => 'Privé aankoop']));
        $this->actingAs(User::factory()->create());
        $this->get(route('spending.index', ['month' => '2026-10']))->assertOk()->assertDontSee('Privé aankoop')->assertViewHas('total', 0);
        $this->get(route('spending.edit', $expense))->assertNotFound();
        $this->put(route('spending.update', $expense), $this->expense())->assertNotFound();
        $this->delete(route('spending.destroy', $expense))->assertNotFound();
        $this->getJson(route('simulator', ['month' => '2026-10']))->assertOk()->assertJsonPath('variable.0.cents', 0);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'name' => 'Privé aankoop']);
    }

    public static function invalidExpenses(): array
    {
        return [
            ['amount', '-1'], ['amount', '0'], ['amount', '1.001'], ['amount', '1e3'], ['amount', '100000000.00'],
            ['amount', 'NaN'], ['amount', '1.000,00'], ['name', ''], ['name', str_repeat('a', 101)],
            ['category', 'housing'], ['expense_date', '2026-02-30'], ['expense_date', 'not-a-date'],
            ['description', str_repeat('x', 2001)],
        ];
    }

    #[DataProvider('invalidExpenses')]
    public function test_invalid_expense_input_is_rejected_on_create_and_update(string $field, mixed $value): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('spending.store'), $this->expense([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('expenses', 0);
        $expense = $user->expenses()->create($this->expense());
        $this->put(route('spending.update', $expense), $this->expense([$field => $value]))->assertSessionHasErrors($field);
        $this->assertSame('24.50', $expense->fresh()->amount);
    }

    public function test_search_category_sorting_pagination_and_filter_validation(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 17) as $index) {
            $user->expenses()->create($this->expense(['name' => 'Aankoop '.$index, 'amount' => (string) $index]));
        }
        $user->expenses()->create($this->expense(['name' => 'Trein', 'amount' => '50.00', 'category' => 'transport']));
        $this->actingAs($user)->get(route('spending.index', ['month' => '2026-10', 'sort' => 'amount', 'direction' => 'asc']))
            ->assertOk()->assertViewHas('entries', fn ($entries) => $entries->total() === 18 && $entries->count() === 15 && $entries->first()->name === 'Aankoop 1');
        $this->get(route('spending.index', ['month' => '2026-10', 'page' => 2, 'sort' => 'amount', 'direction' => 'asc']))
            ->assertViewHas('entries', fn ($entries) => $entries->count() === 3 && $entries->last()->name === 'Trein');
        $this->get(route('spending.index', ['month' => '2026-10', 'search' => 'Trein', 'category' => 'transport']))
            ->assertSee('Trein')->assertDontSee('Aankoop 1')->assertViewHas('total', 20300);
        foreach (['sort' => 'user_id', 'direction' => 'invalid', 'category' => 'housing', 'month' => '2026-13'] as $field => $value) {
            $this->getJson(route('spending.index', [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
    }

    public function test_dashboard_and_simulator_share_monthly_averages_and_actual_expenses_once(): void
    {
        $user = User::factory()->create();
        $user->incomeSources()->create(['name' => 'Salaris', 'amount' => '2500', 'frequency' => 'monthly', 'start_date' => '2026-01-01', 'is_active' => true]);
        $user->fixedExpenses()->create(['name' => 'Huur', 'amount' => '690', 'frequency' => 'monthly', 'category' => 'housing', 'start_date' => '2026-01-01', 'is_active' => true]);
        $user->fixedExpenses()->create(['name' => 'Jaarabonnement', 'amount' => '120', 'frequency' => 'yearly', 'category' => 'subscriptions', 'start_date' => '2026-01-01', 'is_active' => true]);
        $user->expenses()->create($this->expense(['amount' => '450']));
        $user->expenses()->create($this->expense(['amount' => '999', 'expense_date' => '2026-11-01']));
        $this->actingAs($user)->get(route('dashboard', ['month' => '2026-10']))->assertOk()
            ->assertViewHas('income', 250000)->assertViewHas('costs', 70000)->assertViewHas('variableCosts', 45000)
            ->assertViewHas('remaining', 135000)->assertSee('€ 1.350,00')->assertSee('Recente uitgaven');
        $before = [$user->incomeSources()->get()->toArray(), $user->fixedExpenses()->get()->toArray(), $user->expenses()->get()->toArray()];
        $this->get(route('simulator', ['month' => '2026-10']))->assertOk()->assertSee('Scenario resetten');
        $this->getJson(route('simulator', ['month' => '2026-10', 'income' => 999, 'fixed' => []]))->assertOk()
            ->assertJsonPath('income', 250000)->assertJsonPath('fixed.0.cents', 69000)->assertJsonPath('fixed.1.cents', 1000)->assertJsonPath('variable.0.cents', 45000);
        $this->postJson(route('simulator'), ['income' => 0])->assertStatus(405);
        $this->assertSame($before, [$user->incomeSources()->get()->toArray(), $user->fixedExpenses()->get()->toArray(), $user->expenses()->get()->toArray()]);
        // Reset's GET endpoint always reads fresh database data.
        $user->expenses()->first()->update(['amount' => '10.01']);
        $this->getJson(route('simulator', ['month' => '2026-10']))->assertJsonPath('variable.0.cents', 1001);
        $user->expenses()->first()->update(['amount' => '3000.00']);
        $this->assertSame(-120000, app(FinancialOverview::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'))['remaining']);
    }

    public function test_empty_accounts_can_build_a_manual_scenario_and_new_pages_require_authentication(): void
    {
        foreach (['spending.index', 'spending.create', 'simulator'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('spending.store'), $this->expense())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->getJson(route('simulator'))->assertOk()
            ->assertJsonPath('income', 0)->assertJsonCount(0, 'fixed')->assertJsonCount(8, 'variable');
        $this->get(route('spending.index'))->assertOk()->assertViewHas('average', 0)->assertViewHas('largestCategory', null);
    }

    public function test_account_deletion_cascades_actual_expenses(): void
    {
        $user = User::factory()->create();
        $user->expenses()->create($this->expense());
        $this->actingAs($user)->delete(route('settings.destroy'), ['current_password' => 'password'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_theme_initialization_and_toggle_are_present_in_every_layout_before_assets(): void
    {
        foreach (['login', 'register', 'password.request', 'password.reset'] as $route) {
            $response = $this->get(route($route, $route === 'password.reset' ? ['token' => 'test'] : []))->assertOk();
            $response->assertSee('geldgrip-theme')->assertSee('data-theme-toggle', false);
            $this->assertLessThan(strpos($response->getContent(), '<link rel="preload"'), strpos($response->getContent(), 'geldgrip-theme'));
        }
        $this->actingAs(User::factory()->create());
        foreach (['dashboard', 'incomes.index', 'incomes.create', 'expenses.index', 'expenses.create', 'spending.index', 'spending.create', 'simulator', 'settings.edit'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('geldgrip-theme')->assertSee('data-theme-toggle', false);
        }
        $this->get(route('settings.edit'))->assertSee('Systeemvoorkeur')->assertSee('data-theme-option', false);
    }
}
