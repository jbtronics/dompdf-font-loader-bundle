<?php

namespace Services;

use Jbtronics\DompdfFontLoaderBundle\Services\FontFamilyType;
use PHPUnit\Framework\TestCase;

class FontFamilyTypeTest extends TestCase
{

    public function determineTypeDataProvider(): array
    {
        return [
            [FontFamilyType::NORMAL, 'test.ttf'],
            [FontFamilyType::BOLD, 'test_b.ttf'],
            [FontFamilyType::BOLD, 'test_bold.ttf'],
            [FontFamilyType::BOLD, 'test_bd.ttf'],
            [FontFamilyType::ITALIC, 'test_i.ttf'],
            [FontFamilyType::ITALIC, 'test_italic.ttf'],
            [FontFamilyType::ITALIC, 'test_it.ttf'],
            [FontFamilyType::BOLD_ITALIC, 'test_bi.ttf'],
            [FontFamilyType::BOLD_ITALIC, 'test_bold_italic.ttf'],
            [FontFamilyType::BOLD_ITALIC, 'test_bd_it.ttf'],

            //Absolute pathes should also work
            [FontFamilyType::NORMAL, '/var/www/html/test.ttf'],
            [FontFamilyType::BOLD, '/var/www/html/test_b.ttf'],
            [FontFamilyType::BOLD, '/var/www/html/test_bold.ttf'],
        ];
    }

    /**
     * @dataProvider determineTypeDataProvider
     */
    public function testDetermineType(FontFamilyType $expected, string $path): void
    {
        $this->assertEquals($expected, FontFamilyType::determineType($path));
    }
}
