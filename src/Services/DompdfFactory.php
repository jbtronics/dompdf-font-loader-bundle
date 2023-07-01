<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

use Dompdf\Dompdf;

class DompdfFactory implements DompdfFactoryInterface
{
    public function create(): Dompdf
    {
        return new Dompdf();
    }
}