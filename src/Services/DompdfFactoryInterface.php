<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

use Dompdf\Dompdf;

/**
 * You have to implement a service that implements this interface.
 */
interface DompdfFactoryInterface
{
    /**
     * Create a new Dompdf instance, where the font directory and other settings were configured, if needed.
     * @return Dompdf
     */
    public function create(): Dompdf;
}