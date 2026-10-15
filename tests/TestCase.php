<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function admin(array $attrs = []): User
    {
        return User::factory()->admin()->create($attrs);
    }

    protected function agent(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    /** PDF sederhana untuk uji TTE. */
    protected function makePdf(int $pages = 2): string
    {
        $pdf = new \FPDF;
        for ($i = 1; $i <= $pages; $i++) {
            $pdf->AddPage();
            $pdf->SetFont('Helvetica', 'B', 16);
            $pdf->Cell(0, 10, "Dokumen uji halaman {$i}");
        }

        return $pdf->Output('S');
    }
}
