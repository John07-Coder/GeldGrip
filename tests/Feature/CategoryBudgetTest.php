<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BudgetOverview;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryBudgetTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        return array_merge(['category' => 'groceries', 'amount' => '250.00', 'budget_month' => '2026-11'], $overrides);
    }

    public function test_budget_crud_preserves_owner_and_selected_month(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('budgets.store'), $this->data(['amount' => '250,50', 'user_id' => 999]))
            ->assertSessionHasNoErrors()->assertRedirect(route('budgets.index', ['month' => '2026-11']));
        $budget = $user->categoryBudgets()->firstOrFail();
        $this->assertSame('250.50', $budget->amount);
        $this->assertSame($user->id, $budget->user_id);
        $this->get(route('budgets.edit', $budget))->assertOk()->assertSee('2026-11');
        $this->put(route('budgets.update', $budget), $this->data(['amount' => '300.00', 'budget_month' => '2026-12']))
            ->assertSessionHasNoErrors()->assertRedirect(route('budgets.index', ['month' => '2026-12']));
        $this->assertSame(12, $budget->fresh()->month);
        $this->delete(route('budgets.destroy', [$budget, 'month' => '2026-12']))->assertRedirect(route('budgets.index', ['month' => '2026-12']));
        $this->assertDatabaseCount('category_budgets', 0);
    }

    public function test_duplicate_budget_validation_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('budgets.store'), $this->data())->assertSessionHasNoErrors();
        $this->post(route('budgets.store'), $this->data())->assertSessionHasErrors('category');
        $budget = $user->categoryBudgets()->firstOrFail();
        $this->put(route('budgets.update', $budget), $this->data(['amount' => '260']))->assertSessionHasNoErrors();
        $other = $user->categoryBudgets()->create(['category' => 'clothing', 'amount' => '100', 'month' => 11, 'year' => 2026]);
        $this->put(route('budgets.update', $other), $this->data())->assertSessionHasErrors('category');
        $this->post(route('budgets.store'), $this->data(['budget_month' => '2026-12']))->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create())->post(route('budgets.store'), $this->data())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('category_budgets', 4);
    }

    public function test_database_also_prevents_duplicate_budgets(): void
    {
        $user = User::factory()->create();
        $entry = ['category' => 'groceries', 'amount' => '250', 'month' => 11, 'year' => 2026];
        $user->categoryBudgets()->create($entry);
        $this->expectException(UniqueConstraintViolationException::class);
        $user->categoryBudgets()->create($entry);
    }

    public static function invalidBudgets(): array
    {
        return [['amount', '0'], ['amount', '-1'], ['amount', '1.001'], ['amount', '1e3'], ['amount', '100000000'],
            ['category', 'housing'], ['budget_month', '2026-13'], ['budget_month', '0000-01'], ['budget_month', '2026-11-01']];
    }

    #[DataProvider('invalidBudgets')]
    public function test_invalid_budget_input_is_rejected(string $field, string $value): void
    {
        $this->actingAs(User::factory()->create())->post(route('budgets.store'), $this->data([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('category_budgets', 0);
    }

    public static function usageLevels(): array
    {
        return [[69, 'safe', false], [70, 'warning', false], [79, 'warning', false], [80, 'warning', true],
            [89, 'warning', true], [90, 'danger', true], [100, 'danger', true], [125, 'danger', true]];
    }

    #[DataProvider('usageLevels')]
    public function test_usage_thresholds_warnings_and_overrun(int $spent, string $tone, bool $warning): void
    {
        $user = User::factory()->create();
        $user->categoryBudgets()->create(['category' => 'groceries', 'amount' => '100', 'month' => 11, 'year' => 2026]);
        $user->expenses()->create(['name' => 'Boodschappen', 'category' => 'groceries', 'amount' => (string) $spent, 'expense_date' => '2026-11-01']);
        $overview = app(BudgetOverview::class)->forMonth($user, CarbonImmutable::parse('2026-11-01'));
        $row = $overview['rows']->first();
        $this->assertEquals($spent, $row['percentage']);
        $this->assertSame($tone, $row['tone']);
        $this->assertSame($warning, $row['warning'] !== null);
        $this->assertSame((100 - $spent) * 100, $overview['remaining']);
        $this->assertSame($spent > 100 ? 1 : 0, $overview['exceeded']);
        $response = $this->actingAs($user)->get(route('budgets.index', ['month' => '2026-11']))->assertOk();
        if ($spent === 125) {
            $response->assertSee('125% gebruikt')->assertSee('width: 100%', false)->assertSee('Budget overschreden met € 25,00');
        }
    }

    public function test_spending_is_recalculated_and_unbudgeted_categories_are_excluded(): void
    {
        $user = User::factory()->create();
        $user->categoryBudgets()->create(['category' => 'groceries', 'amount' => '250', 'month' => 11, 'year' => 2026]);
        foreach ([['groceries', '175', '2026-11-01'], ['health', '10', '2026-11-01'], ['groceries', '999', '2026-10-31']] as [$category, $amount, $date]) {
            $user->expenses()->create(['name' => 'Aankoop', 'category' => $category, 'amount' => $amount, 'expense_date' => $date]);
        }
        $this->actingAs($user)->get(route('budgets.index', ['month' => '2026-11']))->assertViewHas('spent', 17500)->assertViewHas('remaining', 7500)->assertViewHas('unbudgeted', 1000);
        $data = ['name' => 'Nieuwe aankoop', 'category' => 'groceries', 'amount' => '40', 'expense_date' => '2026-11-12'];
        $this->post(route('spending.store'), $data)->assertSessionHasNoErrors();
        $this->get(route('budgets.index', ['month' => '2026-11']))->assertViewHas('spent', 21500)->assertViewHas('remaining', 3500);
        $expense = $user->expenses()->where('name', 'Nieuwe aankoop')->firstOrFail();
        $this->put(route('spending.update', $expense), array_merge($data, ['expense_date' => '2026-12-01']))->assertSessionHasNoErrors();
        $this->get(route('budgets.index', ['month' => '2026-11']))->assertViewHas('spent', 17500);
        $this->delete(route('spending.destroy', $expense))->assertRedirect();
        $this->get(route('budgets.index', ['month' => '2026-12']))->assertViewHas('spent', 0);
    }

    public function test_copy_previous_handles_year_boundary_and_never_overwrites_or_copies_expenses(): void
    {
        $user = User::factory()->create();
        foreach (['groceries', 'clothing'] as $category) {
            $user->categoryBudgets()->create(['category' => $category, 'amount' => '250', 'month' => 12, 'year' => 2026]);
        }
        $user->categoryBudgets()->create(['category' => 'groceries', 'amount' => '300', 'month' => 1, 'year' => 2027]);
        $user->expenses()->create(['name' => 'December aankoop', 'category' => 'groceries', 'amount' => '40', 'expense_date' => '2026-12-15']);
        User::factory()->create()->categoryBudgets()->create(['category' => 'health', 'amount' => '999', 'month' => 12, 'year' => 2026]);
        $this->actingAs($user)->post(route('budgets.copy'), ['month' => '2027-01'])->assertRedirect(route('budgets.index', ['month' => '2027-01']));
        $this->post(route('budgets.copy'), ['month' => '2027-01'])->assertRedirect();
        $this->assertSame(2, $user->categoryBudgets()->inMonth(CarbonImmutable::parse('2027-01-01'))->count());
        $this->assertSame('300.00', $user->categoryBudgets()->where('year', 2027)->where('category', 'groceries')->first()->amount);
        $this->assertSame(2, $user->categoryBudgets()->where('year', 2026)->count());
        $this->assertDatabaseCount('expenses', 1);
        $this->get(route('budgets.index', ['month' => '2027-02']))->assertSee('Budgetten van vorige maand overnemen');
    }

    public function test_budgets_are_private_and_account_deletion_cascades(): void
    {
        $owner = User::factory()->create();
        $budget = $owner->categoryBudgets()->create(['category' => 'groceries', 'amount' => '250', 'month' => 11, 'year' => 2026]);
        $this->actingAs(User::factory()->create())->get(route('budgets.index', ['month' => '2026-11']))->assertViewHas('total', 0);
        $this->get(route('budgets.edit', $budget))->assertNotFound();
        $this->put(route('budgets.update', $budget), $this->data())->assertNotFound();
        $this->delete(route('budgets.destroy', $budget))->assertNotFound();
        $this->post(route('budgets.copy'), ['month' => '2026-12'])->assertRedirect();
        $this->assertDatabaseCount('category_budgets', 1);
        $this->actingAs($owner)->delete(route('settings.destroy'), ['current_password' => 'password'])->assertRedirect();
        $this->assertDatabaseCount('category_budgets', 0);
    }
}
