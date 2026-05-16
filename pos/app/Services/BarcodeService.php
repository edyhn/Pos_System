<?php

namespace App\Services;

class BarcodeService
{
    /**
     * Generate EAN-13 barcode number from store code + product id.
     * Format: [3-digit country 899=Indonesia][3-digit store][6-digit product][1 check digit]
     */
    public static function generateEAN13(string $storeCode, int $productId): string
    {
        $country = '899'; // Indonesia GS1 prefix
        $store = strtoupper(substr($storeCode, 0, 3));
        $storeNum = str_pad(
            array_sum(array_map('ord', str_split($store))) % 1000,
            3, '0', STR_PAD_LEFT
        );
        $product = str_pad($productId % 1000000, 6, '0', STR_PAD_LEFT);
        $base = $country . $storeNum . $product;

        // Calculate EAN-13 check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$base[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        return $base . $checkDigit;
    }

    /**
     * Render barcode as inline SVG string using native PHP (no external library needed).
     * Generates Code 128B encoding for maximum compatibility.
     */
    public static function toSVG(string $barcode, float $moduleWidth = 1.5, int $height = 50): string
    {
        $encoded = self::encodeCode128B($barcode);
        $totalWidth = count($encoded) * $moduleWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalWidth . ' ' . ($height + 15) . '" width="' . $totalWidth . '" height="' . ($height + 15) . '">';
        $x = 0;
        foreach ($encoded as $bar) {
            if ($bar) {
                $svg .= '<rect x="' . $x . '" y="0" width="' . $moduleWidth . '" height="' . $height . '" fill="#000"/>';
            }
            $x += $moduleWidth;
        }
        // Add text below barcode
        $fontSize = min(12, $totalWidth / strlen($barcode) * 0.8);
        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($height + 12) . '" text-anchor="middle" font-family="monospace" font-size="' . $fontSize . '">' . htmlspecialchars($barcode) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Encode a string using Code 128B.
     * Returns array of 1s (bar) and 0s (space).
     */
    private static function encodeCode128B(string $data): array
    {
        // Code 128B encoding patterns (bars and spaces for each character)
        $patterns = [
            '11011001100', '11001101100', '11001100110', '10010011000', '10010001100',
            '10001001100', '10011001000', '10011000100', '10001100100', '11001001000',
            '11001000100', '11000100100', '10110011100', '10011011100', '10011001110',
            '10111001100', '10011101100', '10011100110', '11001110010', '11001011100',
            '11001001110', '11011100100', '11001110100', '11101101110', '11101001100',
            '11100101100', '11100100110', '11101100100', '11100110100', '11100110010',
            '11011011000', '11011000110', '11000110110', '10100011000', '10001011000',
            '10001000110', '10110001000', '10001101000', '10001100010', '11010001000',
            '11000101000', '11000100010', '10110111000', '10110001110', '10001101110',
            '10111011000', '10111000110', '10001110110', '11101110110', '11010001110',
            '11000101110', '11011101000', '11011100010', '11011101110', '11101011000',
            '11101000110', '11100010110', '11101101000', '11101100010', '11100011010',
            '11101111010', '11001000010', '11110001010', '10100110000', '10100001100',
            '10010110000', '10010000110', '10000101100', '10000100110', '10110010000',
            '10110000100', '10011010000', '10011000010', '10000110100', '10000110010',
            '11000010010', '11001010000', '11110111010', '11000010100', '10001111010',
            '10100111100', '10010111100', '10010011110', '10111100100', '10011110100',
            '10011110010', '11110100100', '11110010100', '11110010010', '11011011110',
            '11011110110', '11110110110', '10101111000', '10100011110', '10001011110',
            '10111101000', '10111100010', '11110101000', '11110100010', '10111011110',
            '10111101110', '11101011110', '11110101110', '11010000100', '11010010000',
            '11010011100', '1100011101011',
        ];

        $startB = 104; // Start Code B
        $result = [];

        // Add Start Code B
        $startPattern = $patterns[$startB];
        foreach (str_split($startPattern) as $bit) {
            $result[] = (int)$bit;
        }

        $checksum = $startB;
        $position = 1;

        foreach (str_split($data) as $char) {
            $value = ord($char) - 32; // Code 128B: ASCII value - 32
            if ($value < 0 || $value > 94) $value = 0; // Fallback to space

            $pattern = $patterns[$value];
            foreach (str_split($pattern) as $bit) {
                $result[] = (int)$bit;
            }

            $checksum += $value * $position;
            $position++;
        }

        // Add checksum character
        $checksumValue = $checksum % 103;
        $checksumPattern = $patterns[$checksumValue];
        foreach (str_split($checksumPattern) as $bit) {
            $result[] = (int)$bit;
        }

        // Add Stop pattern
        $stopPattern = $patterns[106]; // Stop character
        foreach (str_split($stopPattern) as $bit) {
            $result[] = (int)$bit;
        }

        return $result;
    }
}
