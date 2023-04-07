<?php
/**
 * Author   : Nick Jung
 * E-mail   : giahojnost@gmail.com
 * Date     : 2023-04-07 10:32
 */

namespace Giahojnost;

class CreditCard {
    /**
     * @var array|array[]
     */
    protected static array $cards = [
        // Debit cards must come first, since they have more specific patterns than their credit-card equivalents.
        'visaelectron'       => [
            'type'      => 'visaelectron',
            'pattern'   => '/^4(026|17500|405|508|844|91[37])/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'maestro'            => [
            'type'      => 'maestro',
            'pattern'   => '/^(5(018|0[23]|[68])|6(39|7))/',
            'length'    => [12, 13, 14, 15, 16, 17, 18, 19],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'forbrugsforeningen' => [
            'type'      => 'forbrugsforeningen',
            'pattern'   => '/^600/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'dankort'            => [
            'type'      => 'dankort',
            'pattern'   => '/^5019/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        // Credit cards
        'visa'               => [
            'type'      => 'visa',
            'pattern'   => '/^4/',
            'length'    => [13, 16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'mastercard'         => [
            'type'      => 'mastercard',
            'pattern'   => '/^(5[0-5]|2[2-7])/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'amex'               => [
            'type'      => 'amex',
            'pattern'   => '/^3[47]/',
            'format'    => '/(\d{1,4})(\d{1,6})?(\d{1,5})?/',
            'length'    => [15],
            'cvcLength' => [3, 4],
            'luhn'      => true,
        ],
        'dinersclub'         => [
            'type'      => 'dinersclub',
            'pattern'   => '/^3[0689]/',
            'length'    => [14],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'discover'           => [
            'type'      => 'discover',
            'pattern'   => '/^6([045]|22)/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
        'unionpay'           => [
            'type'      => 'unionpay',
            'pattern'   => '/^(62|88)/',
            'length'    => [16, 17, 18, 19],
            'cvcLength' => [3],
            'luhn'      => false,
        ],
        'jcb'                => [
            'type'      => 'jcb',
            'pattern'   => '/^35/',
            'length'    => [16],
            'cvcLength' => [3],
            'luhn'      => true,
        ],
    ];
    
    /**
     * validCreditCard
     * @param $number
     * @param $type
     * @return array
     */
    public static function validCreditCard($number, $type = null): array {
        $ret = [
            'valid'  => false,
            'number' => '',
            'type'   => '',
        ];
        // Strip non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        if (empty($type)) {
            $type = self::creditCardType($number);
        }
        if (array_key_exists($type, self::$cards) && self::validCard($number, $type)) {
            return [
                'valid'  => true,
                'number' => $number,
                'type'   => $type,
            ];
        }
        
        return $ret;
    }
    
    /**
     * validCvc
     * @param $cvc
     * @param $type
     * @return bool
     */
    public static function validCvc($cvc, $type): bool {
        return (ctype_digit($cvc) && array_key_exists($type, self::$cards) && self::validCvcLength($cvc, $type));
    }
    
    /**
     * validDate
     * @param $year
     * @param $month
     * @return bool
     */
    public static function validDate($year, $month): bool {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        if (!preg_match('/^20\d\d$/', $year)) {
            return false;
        }
        if (!preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            return false;
        }
        // past date
        if ($year < date('Y') || $year == date('Y') && $month < date('m')) {
            return false;
        }
        
        return true;
    }
    
    // PROTECTED
    // ---------------------------------------------------------
    /**
     * creditCardType
     * @param $number
     * @return string
     */
    protected static function creditCardType($number): string {
        foreach (self::$cards as $type => $card) {
            if (preg_match($card['pattern'], $number)) {
                return $type;
            }
        }
        
        return '';
    }
    
    /**
     * validCard
     * @param $number
     * @param $type
     * @return bool
     */
    protected static function validCard($number, $type): bool {
        return (self::validPattern($number, $type) && self::validLength($number, $type) && self::validLuhn($number, $type));
    }
    
    /**
     * validPattern
     * @param $number
     * @param $type
     * @return false|int
     */
    protected static function validPattern($number, $type) {
        return preg_match(self::$cards[$type]['pattern'], $number);
    }
    
    /**
     * validLength
     * @param $number
     * @param $type
     * @return bool
     */
    protected static function validLength($number, $type): bool {
        foreach (self::$cards[$type]['length'] as $length) {
            if (strlen($number) == $length) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * validCvcLength
     * @param $cvc
     * @param $type
     * @return bool
     */
    protected static function validCvcLength($cvc, $type): bool {
        foreach (self::$cards[$type]['cvcLength'] as $length) {
            if (strlen($cvc) == $length) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * validLuhn
     * @param $number
     * @param $type
     * @return bool
     */
    protected static function validLuhn($number, $type): bool {
        if (!self::$cards[$type]['luhn']) {
            return true;
        } else {
            return self::luhnCheck($number);
        }
    }
    
    /**
     * luhnCheck
     * @param $number
     * @return bool
     */
    protected static function luhnCheck($number): bool {
        $checksum = 0;
        for ($i = (2 - (strlen($number) % 2)); $i <= strlen($number); $i += 2) {
            $checksum += (int)($number[$i - 1]);
        }
        // Analyze odd digits in even length strings or even digits in odd length strings.
        for ($i = (strlen($number) % 2) + 1; $i < strlen($number); $i += 2) {
            $digit = (int)($number[$i - 1]) * 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += ($digit - 9);
            }
        }
        if (($checksum % 10) == 0) {
            return true;
        } else {
            return false;
        }
    }
}