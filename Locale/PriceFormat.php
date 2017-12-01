<?php
namespace Veriteworks\FixPrice\Locale;

use \Magento\Framework\Locale\Format;
use \Magento\Framework\Locale\Bundle\DataBundle;

class PriceFormat extends Format
{
    /**
     * @var string
     */
    private static $defaultNumberSet = 'latn';

    /**
     * Returns an array with price formatting info
     *
     * @param string $localeCode Locale code.
     * @param string $currencyCode Currency code.
     * @return array
     */
    public function getPriceFormat($localeCode = null, $currencyCode = null)
    {
        $localeCode = $localeCode ?: $this->_localeResolver->getLocale();
        if ($currencyCode) {
            $currency = $this->currencyFactory->create()->load($currencyCode);
        } else {
            $currency = $this->_scopeResolver->getScope()->getCurrentCurrency();
        }

        $localeData = (new DataBundle())->get($localeCode);
        $defaultSet = $localeData['NumberElements']['default'] ?: self::$defaultNumberSet;
        $format = $localeData['NumberElements'][$defaultSet]['patterns']['currencyFormat']
            ?: ($localeData['NumberElements'][self::$defaultNumberSet]['patterns']['currencyFormat']
                ?: explode(';', $localeData['NumberPatterns'][1])[0]);

        $decimalSymbol = $localeData['NumberElements'][$defaultSet]['symbols']['decimal']
            ?: ($localeData['NumberElements'][self::$defaultNumberSet]['symbols']['decimal']
                ?: $localeData['NumberElements'][0]);

        $groupSymbol = $localeData['NumberElements'][$defaultSet]['symbols']['group']
            ?: ($localeData['NumberElements'][self::$defaultNumberSet]['symbols']['group']
                ?: $localeData['NumberElements'][1]);

        $pos = strpos($format, ';');
        if ($pos !== false) {
            $format = substr($format, 0, $pos);
        }
        $format = preg_replace("/[^0\#\.,]/", '', $format);
        $totalPrecision = 0;
        $decimalPoint = strpos($format, '.');
        if ($decimalPoint !== false) {
            $totalPrecision = strlen($format) - (strrpos($format, '.') + 1);
        } else {
            $decimalPoint = strlen($format);
        }
        $requiredPrecision = $totalPrecision;
        $t = substr($format, $decimalPoint);
        $pos = strpos($t, '#');
        if ($pos !== false) {
            $requiredPrecision = strlen($t) - $pos - $totalPrecision;
        }

        if (strrpos($format, ',') !== false) {
            $group = $decimalPoint - strrpos($format, ',') - 1;
        } else {
            $group = strrpos($format, '.');
        }

        $integerRequired = strpos($format, '.') - strpos($format, '0');

        $result = [
            //TODO: change interface
            'pattern' => $currency->getOutputFormat(),
            'precision' => $totalPrecision,
            'requiredPrecision' => $requiredPrecision,
            'decimalSymbol' => $decimalSymbol,
            'groupSymbol' => $groupSymbol,
            'groupLength' => $group,
            'integerRequired' => $integerRequired,
        ];

        return $result;
    }
}