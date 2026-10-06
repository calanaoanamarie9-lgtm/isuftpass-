<?php

namespace Tests\Unit;

use App\Enums\Office;
use Tests\TestCase;

class OfficeTest extends TestCase
{
    public function test_a_typed_name_is_resolved_to_the_office_it_names(): void
    {
        $this->assertSame('Library', Office::fromTyped('library'));
        $this->assertSame('Library', Office::fromTyped('LIBRARY'));
        $this->assertSame('Library', Office::fromTyped('University Library'));
        $this->assertSame('CICI', Office::fromTyped('college of informatics and computing innovations'));
        $this->assertSame('Registrar', Office::fromTyped('Office of the  Registrar'));
    }

    public function test_an_office_that_has_no_case_yet_is_kept_as_typed(): void
    {
        // An office is allowed to apply before it exists in the enum, so its
        // name survives the round trip - only runs of spaces are collapsed.
        $this->assertSame(
            'Property Management Office',
            Office::fromTyped('  Property   Management Office  '),
        );
    }
}
