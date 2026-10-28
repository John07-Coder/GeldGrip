<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinancialOverview;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FinancialManagementTest extends TestCase
{
    use RefreshDatabase;

    private function entry(array $overrides = []): array
    {
        return array_merge(['name' => 'Salaris', 'amount' => '2200.00', 'frequency' => 'monthly',
            'start_date' => '2026-01-01', 'end_date' => null, 'is_active' => true], $overrides);
    }

    private function expense(array $overrides = []): array
    {
        return $this->entry(array_merge(['name' => 'Huur', 'amount' => '650.00', 'category' => 'housing', 'payment_day' => 1], $overrides));
    }

    public function test_income_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('incomes.store'), $this->entry(['amount' => '2200,50', 'user_id' => 999]))->assertSessionHasNoErrors();
        $income = $user->incomeSources()->firstOrFail();
        $this->assertSame('2200.50', $income->amount);
        $this->assertSame($user->id, $income->user_id);
        $this->put(route('incomes.update', $income), $this->entry(['amount' => '2300.00']))->assertSessionHasNoErrors();
        $this->assertSame('2300.00', $income->fresh()->amount);
        $this->delete(route('incomes.destroy', $income))->assertRedirect();
        $this->assertDatabaseMissing('income_sources', ['id' => $income->id]);
    }

    public function test_expense_can_be_created_updated_deactivated_and_deleted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('expenses.store'), $this->expense())->assertSessionHasNoErrors();
        $expense = $user->fixedExpenses()->firstOrFail();
        $this->put(route('expenses.update', $expense), $this->expense(['amount' => '700.00', 'is_active' => false]))->assertSessionHasNoErrors();
        $this->assertFalse($expense->fresh()->is_active);
        $this->assertSame(0, app(FinancialOverview::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'))['costs']);
        $this->delete(route('expenses.destroy', $expense))->assertRedirect();
        $this->assertDatabaseMissing('fixed_expenses', ['id' => $expense->id]);
    }

    public function test_dashboard_calculates_income_expenses_remaining_percentage_and_largest(): void
    {
        $user = User::factory()->create();
        $user->incomeSources()->create($this->entry());
        $user->fixedExpenses()->create($this->expense());
        $user->fixedExpenses()->create($this->expense(['name' => 'Jaarabonnement', 'amount' => '120.00', 'frequency' => 'yearly', 'category' => 'subscriptions']));
        $user->fixedExpenses()->create($this->expense(['name' => 'Wekelijkse kosten', 'amount' => '12.00', 'frequency' => 'weekly', 'category' => 'other']));
        $data = app(FinancialOverview::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'));
        $this->assertSame(220000, $data['income']);
        $this->assertSame(71200, $data['costs']);
        $this->assertSame(148800, $data['remaining']);
        $this->assertSame(67.6, $data['percentage']);
        $this->assertSame('Huur', $data['largest']->name);
        $this->assertSame(71200, $data['categories']->sum('cents'));
        $this->actingAs($user)->get(route('dashboard', ['month' => '2026-10']))->assertOk()->assertSee('€ 1.488,00')->assertSee('67,6%');
    }

    public function test_both_models_filter_inclusive_month_boundaries_and_inactive_records(): void
    {
        $user = User::factory()->create();
        foreach (['incomeSources', 'fixedExpenses'] as $relation) {
            $base = $relation === 'incomeSources' ? $this->entry() : $this->expense();
            foreach ([['name' => 'Begint laatste dag', 'start_date' => '2026-10-31'],
                ['name' => 'Eindigt eerste dag', 'end_date' => '2026-10-01'],
                ['name' => 'Begint volgende maand', 'start_date' => '2026-11-01'],
                ['name' => 'Eindigt vorige maand', 'end_date' => '2026-09-30'],
                ['name' => 'Inactief', 'is_active' => false]] as $overrides) {
                $user->$relation()->create(array_merge($base, $overrides));
            }
            $this->assertSame(['Begint laatste dag', 'Eindigt eerste dag'], $user->$relation()->relevantTo(CarbonImmutable::parse('2026-10-01'))->orderBy('name')->pluck('name')->all());
        }
    }

    public function test_all_record_actions_are_scoped_to_the_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $income = $owner->incomeSources()->create($this->entry(['name' => 'Geheim inkomen']));
        $expense = $owner->fixedExpenses()->create($this->expense(['name' => 'Geheime last']));
        $this->actingAs($intruder);
        foreach ([['incomes', $income, $this->entry()], ['expenses', $expense, $this->expense()]] as [$prefix, $record, $data]) {
            $this->get(route($prefix.'.edit', $record))->assertNotFound();
            $this->put(route($prefix.'.update', $record), $data)->assertNotFound();
            $this->delete(route($prefix.'.destroy', $record))->assertNotFound();
            $this->get(route($prefix.'.index'))->assertOk()->assertDontSee($record->name);
        }
        $this->get(route('dashboard'))->assertDontSee('Geheim inkomen')->assertDontSee('Geheime last');
        $this->assertDatabaseHas('fixed_expenses', ['id' => $expense->id, 'name' => 'Geheime last']);
    }

    public static function invalidEntries(): array
    {
        return [
            'negative' => ['amount', '-1.00'], 'zero' => ['amount', '0'], 'precision' => ['amount', '1.001'],
            'scientific notation' => ['amount', '1e3'], 'too large' => ['amount', '100000000.00'],
            'bad frequency' => ['frequency', 'daily'], 'empty name' => ['name', ''],
            'bad category' => ['category', 'invalid'], 'invalid day' => ['payment_day', 32],
            'decimal day' => ['payment_day', '1.5'], 'end before start' => ['end_date', '2025-12-31'],
            'invalid start' => ['start_date', '2026-02-30'], 'bad status' => ['is_active', 'maybe'],
        ];
    }

    #[DataProvider('invalidEntries')]
    public function test_invalid_expense_input_is_rejected(string $field, mixed $value): void
    {
        $this->actingAs(User::factory()->create())->post(route('expenses.store'), $this->expense([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('fixed_expenses', 0);
    }

    public function test_invalid_income_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post(route('incomes.store'), $this->entry(['amount' => '-20.00', 'frequency' => 'daily']))->assertSessionHasErrors(['amount', 'frequency']);
        $this->assertDatabaseCount('income_sources', 0);
    }

    public function test_search_and_category_filters_and_validation(): void
    {
        $user = User::factory()->create();
        $user->fixedExpenses()->create($this->expense(['name' => 'Internet thuis', 'category' => 'telecom']));
        $user->fixedExpenses()->create($this->expense(['name' => 'Huur appartement', 'category' => 'housing']));
        $this->actingAs($user)->get(route('expenses.index', ['search' => 'Internet', 'category' => 'telecom']))->assertOk()->assertSee('Internet thuis')->assertDontSee('Huur appartement');
        $this->get(route('expenses.index', ['category' => 'insurance']))->assertSee('Geen resultaten gevonden');
        $this->get(route('dashboard', ['month' => '2026-13']))->assertSessionHasErrors('month');
    }

    public function test_negative_and_zero_income_overviews_are_handled(): void
    {
        $user = User::factory()->create();
        $user->fixedExpenses()->create($this->expense());
        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '2026-10']));
        $response->assertOk()->assertSee('− € 650,00');
        $text = preg_replace('/\s+/u', ' ', strip_tags($response->getContent()));
        $this->assertStringContainsString('Je vaste lasten zijn hoger dan je inkomen.', $text);
        $this->assertNull(app(FinancialOverview::class)->forMonth($user, CarbonImmutable::parse('2026-10-01'))['percentage']);
    }

    public function test_forms_and_settings_render(): void
    {
        $user = User::factory()->create();
        $income = $user->incomeSources()->create($this->entry());
        $expense = $user->fixedExpenses()->create($this->expense());
        $this->actingAs($user);
        foreach ([route('incomes.create'), route('expenses.create'), route('incomes.edit', $income), route('expenses.edit', $expense), route('settings.edit')] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_account_deletion_requires_password_and_cascades_financial_records(): void
    {
        $user = User::factory()->create();
        $user->incomeSources()->create($this->entry());
        $user->fixedExpenses()->create($this->expense());
        $this->actingAs($user)->delete(route('settings.destroy'), ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->assertDatabaseCount('users', 1);
        $this->delete(route('settings.destroy'), ['current_password' => 'password'])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('income_sources', 0);
        $this->assertDatabaseCount('fixed_expenses', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_selected_month_is_preserved_after_saving_and_changes_the_overview(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('incomes.store'), $this->entry(['month' => '2026-11', 'start_date' => '2026-11-01']))
            ->assertRedirect(route('incomes.index', ['month' => '2026-11']));
        $this->get(route('dashboard', ['month' => '2026-10']))->assertOk()->assertViewHas('income', 0);
        $this->get(route('dashboard', ['month' => '2026-11']))->assertOk()->assertViewHas('income', 220000);
    }

    public function test_profile_and_password_can_be_changed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('settings.update'), ['name' => 'Nieuwe naam', 'email' => 'nieuw@example.test'])->assertSessionHasNoErrors();
        $this->assertSame('Nieuwe naam', $user->fresh()->name);
        $this->put(route('settings.password'), ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('current_password');
        $this->put(route('settings.password'), ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_dashboard_actions_return_to_an_updated_dashboard(): void
    {
        $user = User::factory()->create();
        $context = ['month' => '2026-10', 'return_to' => 'dashboard'];
        $dashboard = route('dashboard', ['month' => '2026-10']);
        $this->actingAs($user)->post(route('expenses.store'), $this->expense($context))->assertRedirect($dashboard);
        $expense = $user->fixedExpenses()->firstOrFail();
        $this->get($dashboard)->assertViewHas('costs', 65000);
        $this->put(route('expenses.update', $expense), $this->expense($context + ['amount' => '700.00']))->assertRedirect($dashboard);
        $this->get($dashboard)->assertViewHas('costs', 70000);
        $this->delete(route('expenses.destroy', [$expense, ...$context]))->assertRedirect($dashboard);
        $this->get($dashboard)->assertViewHas('costs', 0);
    }
}
