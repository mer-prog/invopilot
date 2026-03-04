<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\InvoiceStatus;
use App\Enums\OrganizationRole;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'locale' => 'ja',
            'timezone' => 'Asia/Tokyo',
        ]);

        $organizations = $this->createOrganizations($testUser);

        foreach ($organizations as $organization) {
            $this->seedOrganization($organization, $testUser);
        }
    }

    /**
     * @return array<Organization>
     */
    private function createOrganizations(User $owner): array
    {
        $orgs = [
            [
                'name' => 'Acme Corporation',
                'slug' => 'acme-corp',
                'default_currency' => Currency::Usd,
                'invoice_prefix' => 'ACM',
            ],
            [
                'name' => '株式会社サクラテック',
                'slug' => 'sakura-tech',
                'default_currency' => Currency::Jpy,
                'invoice_prefix' => 'SKR',
            ],
            [
                'name' => 'Global Trading Ltd',
                'slug' => 'global-trading',
                'default_currency' => Currency::Eur,
                'invoice_prefix' => 'GLB',
            ],
        ];

        $result = [];
        foreach ($orgs as $orgData) {
            $org = Organization::factory()->create(array_merge($orgData, [
                'owner_id' => $owner->id,
            ]));

            $org->users()->attach($owner->id, ['role' => OrganizationRole::Owner->value]);

            $members = User::factory()->count(fake()->numberBetween(2, 4))->create();
            foreach ($members as $member) {
                $org->users()->attach($member->id, [
                    'role' => fake()->randomElement([OrganizationRole::Admin->value, OrganizationRole::Member->value]),
                ]);
            }

            $result[] = $org;
        }

        return $result;
    }

    private function seedOrganization(Organization $org, User $owner): void
    {
        $clients = Client::factory()
            ->count(fake()->numberBetween(5, 10))
            ->create(['organization_id' => $org->id]);

        $invoiceNumber = 1;
        $invoiceCount = fake()->numberBetween(20, 50);

        $statusDistribution = [
            InvoiceStatus::Draft,
            InvoiceStatus::Draft,
            InvoiceStatus::Sent,
            InvoiceStatus::Sent,
            InvoiceStatus::Sent,
            InvoiceStatus::Paid,
            InvoiceStatus::Paid,
            InvoiceStatus::Paid,
            InvoiceStatus::Paid,
            InvoiceStatus::Overdue,
            InvoiceStatus::Overdue,
            InvoiceStatus::Cancelled,
        ];

        for ($i = 0; $i < $invoiceCount; $i++) {
            $status = $statusDistribution[array_rand($statusDistribution)];
            $client = $clients->random();
            $number = $org->invoice_prefix.'-'.str_pad((string) $invoiceNumber, 5, '0', STR_PAD_LEFT);
            $invoiceNumber++;

            $invoice = $this->createInvoice($org, $client, $number, $status);

            $itemCount = fake()->numberBetween(1, 5);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $item = InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                    'sort_order' => $j,
                ]);
                $subtotal += (float) $item->amount;
            }

            $taxAmount = round($subtotal * 0.1, 2);
            $discountAmount = fake()->optional(0.2)->randomFloat(2, 0, $subtotal * 0.05) ?? 0;
            $total = round($subtotal + $taxAmount - $discountAmount, 2);

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            if ($status === InvoiceStatus::Paid) {
                Payment::factory()->create([
                    'invoice_id' => $invoice->id,
                    'amount' => $total,
                    'paid_at' => $invoice->paid_at,
                ]);
            }

            ActivityLog::factory()->create([
                'organization_id' => $org->id,
                'user_id' => $owner->id,
                'action' => 'created',
                'subject_type' => Invoice::class,
                'subject_id' => $invoice->id,
                'description' => "Invoice {$number} created",
            ]);
        }

        $org->update(['invoice_next_number' => $invoiceNumber]);

        $recurringCount = fake()->numberBetween(2, 5);
        for ($i = 0; $i < $recurringCount; $i++) {
            RecurringInvoice::factory()->create([
                'organization_id' => $org->id,
                'client_id' => $clients->random()->id,
            ]);
        }
    }

    private function createInvoice(Organization $org, Client $client, string $number, InvoiceStatus $status): Invoice
    {
        $factory = Invoice::factory()->state([
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'invoice_number' => $number,
            'currency' => $org->default_currency,
        ]);

        return match ($status) {
            InvoiceStatus::Draft => $factory->draft()->create(),
            InvoiceStatus::Sent => $factory->sent()->create(),
            InvoiceStatus::Paid => $factory->paid()->create(),
            InvoiceStatus::Overdue => $factory->overdue()->create(),
            InvoiceStatus::Cancelled => $factory->cancelled()->create(),
        };
    }
}
