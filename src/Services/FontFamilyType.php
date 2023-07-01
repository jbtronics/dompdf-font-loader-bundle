<?php

namespace Jbtronics\DompdfFontLoaderBundle\Services;

enum FontFamilyType: string
{
    case NORMAL = 'normal';
    case BOLD = 'bold';
    case ITALIC = 'italic';
    case BOLD_ITALIC = 'bold_italic';

    /**
     * This function determines the font family type (normal, bold, italic, bold_italic) from the given path to a font file.
     * It is determined based on the suffix of the file name.
     * Bold => _b, _bold, _bd
     * Italic => _i, _italic, _it
     * Bold_Italic => _bi, _bold_italic, _bd_it
     * Everything else is considered normal.
     * @param  string  $font_path
     * @return FontFamilyType
     */
    public static function determineType(string $font_path): FontFamilyType
    {
        //We just consider the file name, without extension and path
        $font_file_name = pathinfo($font_path, PATHINFO_FILENAME);

        //Check if the file name contains a suffix for bold_italic
        //We have to check this first, because the other checks would also match for bold_italic
        if (preg_match('/_b(ol)?d?_?i(t)?(al)?(ic)?$/i', $font_file_name)) {
            return FontFamilyType::BOLD_ITALIC;
        }
        //Check if the file name contains a suffix for bold
        if (preg_match('/_b(ol)?d?$/i', $font_file_name)) {
            return FontFamilyType::BOLD;
        }
        //Check if the file name contains a suffix for italic
        if (preg_match('/_i(t)?(al)?(ic)?$/i', $font_file_name)) {
            return FontFamilyType::ITALIC;
        }

        //If nothing else matches, we consider it normal
        return FontFamilyType::NORMAL;
    }
}
