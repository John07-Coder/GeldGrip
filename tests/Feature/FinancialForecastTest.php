<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinancialForecast;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialForecastTest extends TestCase
{
    use RefreshDatabase;

    private function userWithHistory(): User
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-31 12:00'));
        $user = User::factory()->create();
        $user->incomeSources()->create(['name' => 'Salaris', 'amount' => '2500', 'frequency' => 'monthly', 'start_date' => '2026-01-01', 'is_active' => true]);
        $user->fixedExpenses()->create(['name' => 'Huur', 'amount' => '690', 'category' => 'housing', 'frequency' => 'monthly', 'start_date' => '2026-01-01', 'is_active' => true]);
        $user->fixedExpenses()->create(['name' => 'Jaarabonnement', 'amount' => '120', 'category' => 'subscriptions', 'frequency' => 'yearly', 'start_date' => '2026-01-01', 'is_active' => true]);
        foreach (['2026-07-10', '2026-08-10', '2026-09-30'] as $date) {
            $user->expenses()->create(['name' => 'Boodschappen', 'amount' => '450', 'category' => 'groceries', 'expense_date' => $date]);
        }

        return $user;
    }

    public function test_projection_for_three_six_and_twelve_months_and_dashboard_match(): void
    {
        $user = $this->userWithHistory();
        $this->actingAs($user);
        foreach ([3 => 405000, 6 => 810000, 12 => 1620000] as $horizon => $total) {
            $this->getJson(route('forecasts', ['month' => '2026-10', 'horizon' => $horizon]))->assertOk()
                ->assertJsonCount($horizon, 'projection')->assertJsonPath('projection.0.income', 250000)
                ->assertJsonPath('projection.0.fixed', 70000)->assertJsonPath('projection.0.variable', 45000)
                ->assertJsonPath('projection.0.remaining', 135000)->assertJsonPath('projection.'.($horizon - 1).'.cumulative', $total);
        }
        $this->get(route('forecasts'))->assertOk()->assertViewHas('horizon', 6)->assertSee('€ 8.100,00')->assertSee('geen daadwerkelijk banksaldo');
        $this->get(route('dashboard', ['month' => '2026-10']))->assertOk()->assertViewHas('forecastTotal', 810000)->assertSee('Je eerste budget toevoegen');
    }

    public function test_history_uses_last_three_complete_months_with_data_and_excludes_current_future_and_other_users(): void
    {
        $user = $this->userWithHistory();
        foreach (['2026-06-30', '2026-10-01', '2026-10-31', '2026-11-01'] as $date) {
            $user->expenses()->create(['name' => 'Niet meenemen', 'amount' => '9999', 'category' => 'other', 'expense_date' => $date]);
        }
        User::factory()->create()->expenses()->create(['name' => 'Privé uitgave', 'amount' => '9999', 'category' => 'other', 'expense_date' => '2026-09-30']);
        $baseline = app(FinancialForecast::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'));
        $this->assertSame(45000, $baseline['variable']);
        $this->assertSame(['2026-09', '2026-08', '2026-07'], array_column($baseline['history'], 'month'));
        $futureReference = app(FinancialForecast::class)->forMonth($user, CarbonImmutable::parse('2027-01-01'));
        $this->assertSame(45000, $futureReference['variable']);
    }

    public function test_sparse_history_empty_history_and_cent_rounding_are_explained(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-31'));
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('forecasts'))->assertOk()->assertSee('Nog geen bruikbare uitgavenhistorie');
        $user->expenses()->create(['name' => 'A', 'amount' => '0.01', 'category' => 'other', 'expense_date' => '2026-01-01']);
        $this->getJson(route('forecasts'))->assertJsonPath('baseline.variable', 1)->assertJsonCount(1, 'baseline.history');
        $user->expenses()->create(['name' => 'B', 'amount' => '0.02', 'category' => 'other', 'expense_date' => '2026-09-01']);
        $this->getJson(route('forecasts'))->assertJsonPath('baseline.variable', 2)->assertJsonCount(2, 'baseline.history');
        $this->get(route('forecasts'))->assertSee('2 volledige maanden')->assertSee('Maanden zonder geregistreerde uitgaven');
        $past = app(FinancialForecast::class)->forMonth($user, CarbonImmutable::parse('2026-02-01'));
        $this->assertSame(['2026-01'], array_column($past['history'], 'month'));
    }

    public function test_monthly_projection_respects_inclusive_start_end_and_active_status(): void
    {
        $user = $this->userWithHistory();
        $user->incomeSources()->first()->update(['end_date' => '2027-01-31']);
        $user->incomeSources()->create(['name' => 'Nieuwe baan', 'amount' => '3000', 'frequency' => 'monthly', 'start_date' => '2027-03-31', 'is_active' => true]);
        $user->incomeSources()->create(['name' => 'Inactief', 'amount' => '9999', 'frequency' => 'monthly', 'start_date' => '2026-01-01', 'is_active' => false]);
        $user->fixedExpenses()->where('name', 'Huur')->update(['end_date' => '2026-12-01']);
        $this->actingAs($user)->getJson(route('forecasts', ['month' => '2026-10']))->assertOk()
            ->assertJsonPath('projection.1.fixed', 70000)->assertJsonPath('projection.2.fixed', 1000)
            ->assertJsonPath('projection.2.income', 250000)->assertJsonPath('projection.3.income', 0)
            ->assertJsonPath('projection.3.remaining', -46000)->assertJsonPath('projection.4.income', 300000);
        foreach (range(1, 12) as $offset) {
            $month = CarbonImmutable::parse('2026-10-01')->addMonths($offset);
            $queryIds = $user->incomeSources()->relevantTo($month)->pluck('id')->all();
            $loadedIds = $user->incomeSources()->get()->filter(fn ($entry) => $entry->isRelevantTo($month))->pluck('id')->all();
            $this->assertSame($queryIds, $loadedIds);
        }
    }

    public function test_assumptions_zero_values_reset_and_negative_results_never_write_financial_data(): void
    {
        $user = $this->userWithHistory();
        $before = [$user->incomeSources()->get()->toArray(), $user->fixedExpenses()->get()->toArray(), $user->expenses()->get()->toArray()];
        $this->actingAs($user)->getJson(route('forecasts', ['income' => '2800,00', 'fixed' => '900', 'variable' => '500', 'horizon' => 3]))
            ->assertOk()->assertJsonPath('projection.2.cumulative', 420000);
        $this->getJson(route('forecasts', ['income' => '0', 'variable' => '0', 'horizon' => 12]))->assertOk()->assertJsonPath('projection.11.cumulative', -840000);
        $this->getJson(route('forecasts'))->assertJsonPath('assumptions.income', null)->assertJsonPath('projection.5.cumulative', 810000);
        $this->postJson(route('forecasts'), ['income' => '0'])->assertStatus(405);
        $this->assertSame($before, [$user->incomeSources()->get()->toArray(), $user->fixedExpenses()->get()->toArray(), $user->expenses()->get()->toArray()]);
        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_forecast_input_is_validated_and_new_pages_require_authentication(): void
    {
        foreach (['forecasts', 'budgets.index', 'budgets.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->post(route('budgets.copy'), ['month' => '2026-11'])->assertRedirect(route('login'));
        $user = $this->userWithHistory();
        $this->actingAs($user);
        foreach (['income' => '-1', 'fixed' => '1.001', 'variable' => '1e3', 'horizon' => 4, 'month' => '2026-13'] as $field => $value) {
            $this->getJson(route('forecasts', [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->actingAs(User::factory()->create())->getJson(route('forecasts'))->assertOk()->assertJsonPath('projection.0.income', 0)->assertJsonPath('baseline.variable', 0);
    }

    public function test_dashboard_shows_at_most_three_budgets_in_order_and_theme_assets_remain_present(): void
    {
        $user = $this->userWithHistory();
        foreach (['groceries' => 90, 'clothing' => 70, 'dining' => 40, 'health' => 20] as $category => $spent) {
            $user->categoryBudgets()->create(['category' => $category, 'amount' => '100', 'month' => 10, 'year' => 2026]);
            $user->expenses()->create(['name' => 'Aankoop', 'amount' => (string) $spent, 'category' => $category, 'expense_date' => '2026-10-12']);
        }
        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '2026-10']))->assertOk();
        $response->assertSeeInOrder(['90% gebruikt', '70% gebruikt', '40% gebruikt'])->assertDontSee('20% gebruikt');
        foreach (['budgets.index', 'budgets.create', 'forecasts'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('geldgrip-theme')->assertSee('data-theme-toggle', false);
        }
    }
}
