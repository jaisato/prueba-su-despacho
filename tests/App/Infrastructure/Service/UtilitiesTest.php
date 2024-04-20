<?php

declare(strict_types=1);

namespace Tests\App\Infrastructure\Service;

use App\Infrastructure\Service\Utilities;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UtilitiesTest extends KernelTestCase
{
    public function testQuitarPalabrasCortasTextoBuscadorSuccess(): void
    {
        $result = Utilities::quitarPalabrasCortasTexto('el otro yo');

        $this->assertEquals('otro yo', $result);
    }
}
