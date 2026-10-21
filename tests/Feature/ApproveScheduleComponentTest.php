<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The approve popover's Alpine component.
 *
 * It used to live inside the x-data attribute of the element that carries
 * it. That is a quoting minefield: one unescaped quote turns the whole
 * expression into a syntax error, and Alpine then drops the component
 * silently - the button simply does nothing, with nothing on screen to say
 * why. The component now lives in resources/js/approve-schedule.js, where
 * Vite compiles it, so a broken expression fails the build.
 *
 * These tests guard the seam: the view must call the registered provider,
 * and the provider's file must parse.
 */
class ApproveScheduleComponentTest extends TestCase
{
    use RefreshDatabase;

    private function registrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function pendingAppointment(): Appointment
    {
        $student = User::factory()->create(['role' => 'student']);

        return $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => null,
            'status' => 'pending',
        ]);
    }

    private function approvedAppointment(): Appointment
    {
        $appointment = $this->pendingAppointment();

        $appointment->update([
            'status' => 'confirmed',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'confirmed_time' => '9:00 AM',
            'confirmed_at' => now(),
        ]);

        return $appointment;
    }

    /**
     * Render the component on its own and hand back its markup, so the chip
     * and the button can be read by their own text rather than by a
     * substring of the whole page.
     */
    private function renderMarkup(Appointment $appointment): string
    {
        $this->withViewErrors([]);

        return Blade::render(
            '<x-approve-schedule :appointment="$appointment" />',
            ['appointment' => $appointment]
        );
    }

    /**
     * The same markup, parsed, so the chip and the button can be found by
     * their own text.
     */
    private function renderComponent(Appointment $appointment): \DOMDocument
    {
        $dom = new \DOMDocument;

        @$dom->loadHTML($this->renderMarkup($appointment));

        return $dom;
    }

    private function buttonTexts(\DOMDocument $dom): array
    {
        $texts = [];

        foreach ($dom->getElementsByTagName('button') as $button) {
            $texts[] = trim(preg_replace('/\s+/', ' ', $button->textContent));
        }

        return $texts;
    }

    public function test_the_popover_calls_the_registered_provider(): void
    {
        $appointment = $this->pendingAppointment();

        // The component on its own, so the layout's own x-data attribute
        // cannot be mistaken for this one's. The error bag is shared the
        // way the middleware shares it on a real request.
        $this->withViewErrors([]);

        $html = Blade::render(
            '<x-approve-schedule :appointment="$appointment" />',
            ['appointment' => $appointment]
        );

        // The provider is called, not inlined: no JavaScript object literal
        // is buried in the attribute any more.
        $this->assertStringContainsString('x-data="approveSchedule(', $html);
        $this->assertStringNotContainsString('x-data="{', $html);

        // The office's hours reach the component as arguments.
        $this->assertStringContainsString("officeOpen: '08:00'", $html);
        $this->assertStringContainsString("officeClose: '17:00'", $html);
    }

    public function test_the_provider_file_parses_as_javascript(): void
    {
        $path = resource_path('js/approve-schedule.js');

        $this->assertTrue(File::exists($path), 'The approve-schedule provider is missing.');

        $source = File::get($path);

        // A provider that never registers itself leaves the view calling a
        // function that does not exist, which fails exactly as silently.
        $this->assertStringContainsString("Alpine.data('approveSchedule'", $source);

        $this->assertJavaScriptParses($source);
    }

    /**
     * An answered booking shows the state and nothing else: the chip reads
     * "Rescheduled" and stops there, with no button beside it.
     */
    public function test_an_answered_booking_shows_the_chip_alone(): void
    {
        $dom = $this->renderComponent($this->approvedAppointment());

        $buttons = $this->buttonTexts($dom);

        $this->assertNotContains('Reschedule', $buttons);
        $this->assertNotContains('Edit', $buttons);

        // The chip itself names the state and stops there.
        $chip = (new \DOMXPath($dom))->query("//*[contains(@class, 'bg-emerald-50')]")->item(0);

        $this->assertNotNull($chip, 'The Rescheduled chip is missing.');
        $this->assertSame('Rescheduled', trim(preg_replace('/\s+/', ' ', $chip->textContent)));
    }

    /**
     * The chip is the trigger for the reschedule modal: it announces the
     * booking it means, and the modal beside it is the thing that opens.
     */
    public function test_the_chip_opens_the_reschedule_modal(): void
    {
        $appointment = $this->approvedAppointment();

        $dom = $this->renderComponent($appointment);

        $chip = (new \DOMXPath($dom))->query("//*[contains(@class, 'bg-emerald-50')]")->item(0);

        $this->assertNotNull($chip, 'The Rescheduled chip is missing.');

        // The chip announces the booking it means; the modal beside it is
        // the thing that opens.
        $markup = $this->renderMarkup($appointment);

        $this->assertStringContainsString(
            "\$dispatch('open-reschedule', { id: ".$appointment->id.' })',
            $markup,
        );

        // The chip opens nothing of its own: there is no card of its own
        // behind it any more.
        $card = (new \DOMXPath($dom))->query("//*[@x-show='details']")->item(0);

        $this->assertNull($card, 'The chip still carries a card of its own.');
    }

    /**
     * Parse the source with Node, the same engine that runs it in the
     * browser. Skipped when Node is not installed - the build still catches
     * a broken file there.
     */
    private function assertJavaScriptParses(string $source): void
    {
        $node = $this->nodeBinary();

        if ($node === null) {
            $this->markTestSkipped('Node is not installed, so the JavaScript cannot be parsed here.');
        }

        $file = tempnam(sys_get_temp_dir(), 'approve-schedule').'.js';

        File::put($file, $source);

        try {
            exec(escapeshellarg($node).' --check '.escapeshellarg($file).' 2>&1', $output, $status);

            $this->assertSame(
                0,
                $status,
                "The approve-schedule provider is not valid JavaScript:\n".implode("\n", $output)
            );
        } finally {
            File::delete($file);
        }
    }

    private function nodeBinary(): ?string
    {
        // Node ships as node.exe on Windows and node everywhere else.
        $names = DIRECTORY_SEPARATOR === '\\'
            ? ['node.exe', 'node.cmd', 'node']
            : ['node'];

        $path = (string) (getenv('PATH') ?: '');

        foreach (explode(PATH_SEPARATOR, $path) as $directory) {
            foreach ($names as $name) {
                $candidate = rtrim($directory, '\\/').DIRECTORY_SEPARATOR.$name;

                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
