<?php
declare(strict_types=1);

namespace Loxya\Tests;

use Brick\Math\BigDecimal as Decimal;
use Illuminate\Support\Carbon;
use Loxya\Errors\Exception\ValidationException;
use Loxya\Models\Beneficiary;
use Loxya\Models\Event;
use Loxya\Models\Invoice;
use Loxya\Models\User;
use Loxya\Services\I18n;
use Loxya\Support\Pdf;

final class InvoiceTest extends TestCase
{
    public function testValidation(): void
    {
        $invoice = new Invoice([
            'number' => '',
            'date' => '',
            'booking_start_date' => '',
            'booking_end_date' => '',
            'degressive_rate' => 100_000.0,
            'vat_rate' => -5.0,
            'total_without_taxes' => 1_000_000_000_000,
            'total_replacement' => -20,
            'currency' => 'a',
        ]);
        $invoice->booking()->associate(Event::findOrFail(1));
        $invoice->beneficiary()->associate(Beneficiary::findOrFail(1));
        $errors = $invoice->validationErrors();

        $expectedErrors = [
            'number' => ["Ce champ est obligatoire."],
            'date' => ["Ce champ est obligatoire.", "Cette date est invalide."],
            'degressive_rate' => ["Ce champ est invalide."],
            'discount_rate' => ["Ce champ doit contenir un chiffre à virgule."],
            'vat_rate' => ["Ce champ est invalide."],
            'total_replacement' => ["Ce champ est invalide."],
            'currency' => [
                "Toutes les règles requises doivent être validées\xc2\xa0:",
                "Ce champ doit être en majuscule.",
                "3 caractères attendus.",
            ],
            'booking_start_date' => ["Cette date est invalide."],
            'booking_end_date' => ["Cette date est invalide."],
            'daily_total' => ["Ce champ doit contenir un chiffre à virgule."],
            'total_without_discount' => ["Ce champ doit contenir un chiffre à virgule."],
            'total_discountable' => ["Ce champ doit contenir un chiffre à virgule."],
            'total_discount' => ["Ce champ doit contenir un chiffre à virgule."],
            'total_without_taxes' => ["Ce champ est invalide."],
            'total_taxes' => ["Ce champ doit contenir un chiffre à virgule."],
            'total_with_taxes' => ["Ce champ doit contenir un chiffre à virgule."],
        ];
        $this->assertEquals($expectedErrors, $errors);

        // - Test de validation du numéro de facture et du taux de remise.
        $invoice = new Invoice([
            'number' => '2020-00001',
            'date' => '2024-01-19 16:00:00',
            'booking_start_date' => '2018-12-17 00:00:00',
            'booking_end_date' => '2018-12-18 23:59:59',
            'degressive_rate' => 1.75,
            'discount_rate' => 50.0,
            'vat_rate' => 20.0,
            'daily_total' => 1000.0,
            'total_without_discount' => 1750.0,
            'total_discountable' => 437.5, // => 25% de remise max.
            'total_discount' => 875.0,
            'total_without_taxes' => 875.0,
            'total_taxes' => 175.0,
            'total_with_taxes' => 1050.0,
            'currency' => 'EUR',
            'total_replacement' => 2000,
        ]);
        $invoice->booking()->associate(Event::findOrFail(1));
        $invoice->beneficiary()->associate(Beneficiary::findOrFail(1));
        $errors = $invoice->validationErrors();

        $expectedErrors = [
            'number' => ["Une facture existe déjà avec ce numéro."],
            'discount_rate' => ["Le taux de remise dépasse le maximum."],
        ];
        $this->assertEquals($expectedErrors, $errors);
    }

    public function testCreateFromEventBadDiscountRate(): void
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 22, 18, 42, 36));

        $event = tap(Event::findOrFail(2), function ($event) {
            // - Pour cet événement, le taux de remise maximum est de 5.6328 %
            $event->discount_rate = Decimal::of('5.3629');
        });

        $this->expectException(ValidationException::class);
        Invoice::createFromBooking($event, User::findOrFail(1));
    }

    public function testCreateFromEvent(): void
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 22, 18, 42, 36));

        $event = tap(Event::findOrFail(2), function ($event) {
            $event->discount_rate = Decimal::of('1.3923');
        });
        $result = Invoice::createFromBooking($event, User::findOrFail(1));
        $expected = [
            'id' => 2,
            'number' => '2022-00001',
            'url' => 'http://loxya.test/invoices/2/pdf',
            'date' => '2022-10-22 18:42:36',
            'booking_type' => Event::TYPE,
            'booking_id' => 2,
            'booking_title' => 'Second événement',
            'booking_start_date' => '2018-12-18 00:00:00',
            'booking_end_date' => '2018-12-19 23:59:59',
            'beneficiary_id' => 3,
            'materials' => [
                [
                    'id' => 3,
                    'invoice_id' => 2,
                    'material_id' => 1,
                    'name' => 'Console Yamaha CL3',
                    'reference' => 'CL3',
                    'unit_price' => '300.00',
                    'total_price' => '900.00',
                    'replacement_price' => '19400.00',
                    'is_hidden_on_bill' => false,
                    'is_discountable' => false,
                    'quantity' => 3,
                ],
                [
                    'id' => 4,
                    'invoice_id' => 2,
                    'material_id' => 2,
                    'name' => 'Processeur DBX PA2',
                    'reference' => 'DBXPA2',
                    'unit_price' => '25.50',
                    'total_price' => '51.00',
                    'replacement_price' => '349.90',
                    'is_hidden_on_bill' => false,
                    'is_discountable' => true,
                    'quantity' => 2,
                ],
            ],
            'degressive_rate' => '1.75',
            'discount_rate' => '1.3923',
            'vat_rate' => '20.00',

            // - Total / jour.
            'daily_total' => '951.00',

            // - Remise.
            'total_without_discount' => '1664.25',
            'total_discountable' => '89.25',
            'total_discount' => '23.17',

            // - Totaux.
            'total_without_taxes' => '1641.08',
            'total_taxes' => '328.22',
            'total_with_taxes' => '1969.30',

            'total_replacement' => '58899.80',
            'currency' => 'EUR',
            'author_id' => 1,
            'created_at' => '2022-10-22 18:42:36',
            'updated_at' => '2022-10-22 18:42:36',
            'deleted_at' => null,
        ];
        $result = $result->append('materials')->attributesToArray();
        $this->assertEquals($expected, $result);
    }

    public function testToPdf(): void
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 22, 18, 42, 36));

        $invoice = Invoice::findOrFail(1);
        $result = $invoice->toPdf(new I18n('fr'));
        $this->assertInstanceOf(Pdf::class, $result);
        $this->assertSame('facture-testing-corp-2020-00001-jean-fountain.pdf', $result->getName());
        $this->assertMatchesHtmlSnapshot($result->getRawContent());

        // - La même chose mais formaté pour la Suisse.
        $invoice = Invoice::findOrFail(1);
        $result = $invoice->toPdf(new I18n('fr_CH'));
        $this->assertInstanceOf(Pdf::class, $result);
        $this->assertMatchesHtmlSnapshot($result->getRawContent());
    }

    public function testGetLastNumber(): void
    {
        $result = Invoice::getLastNumber(2099);
        $this->assertEquals(null, $result);

        $result = Invoice::getLastNumber(2020);
        $this->assertEquals('2020-00001', $result);
    }

    public function testGetNextNumber(): void
    {
        $result = Invoice::getNextNumber(2099);
        $this->assertEquals('2099-00001', $result);

        $result = Invoice::getNextNumber(2020);
        $this->assertEquals('2020-00002', $result);
    }
}
