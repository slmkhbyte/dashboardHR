<?php

namespace Tests\Unit;

use App\Support\ImportDateParser;
use PHPUnit\Framework\TestCase;

class ImportDateParserTest extends TestCase
{
    public function test_it_preserves_iso_dates(): void
    {
        $this->assertSame('1994-03-12', ImportDateParser::parse('1994-03-12'));
    }

    public function test_it_parses_localized_date_formats(): void
    {
        $this->assertSame('1994-03-12', ImportDateParser::parse('12/03/1994'));
        $this->assertSame('1994-03-12', ImportDateParser::parse('12-03-1994'));
        $this->assertSame('1994-03-12', ImportDateParser::parse('12.03.1994'));
    }

    public function test_it_parses_localized_dates_with_time(): void
    {
        $this->assertSame('1994-03-12', ImportDateParser::parse('12/03/1994 08:30:45'));
    }

    public function test_it_parses_short_excel_style_dates(): void
    {
        $this->assertSame('1994-03-12', ImportDateParser::parse('03-12-94'));
        $this->assertSame('2026-05-28', ImportDateParser::parse('05-28-26'));
        $this->assertSame('1994-03-12', ImportDateParser::parse('"03-12-94"'));
        $this->assertSame('2026-05-28', ImportDateParser::parse("'05-28-26'"));
    }

    public function test_it_parses_excel_serial_dates(): void
    {
        $this->assertSame('2023-12-31', ImportDateParser::parse('45291'));
    }

    public function test_it_returns_null_for_blank_values(): void
    {
        $this->assertNull(ImportDateParser::parse(''));
        $this->assertNull(ImportDateParser::parse('   '));
    }

    public function test_it_leaves_invalid_values_for_validation_to_reject(): void
    {
        $this->assertSame('abc', ImportDateParser::parse('abc'));
    }
}
