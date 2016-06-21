<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

/**
 * Convert standard format to flat format for variant group
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantGroup extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /** @var ProductValueConverter */
    protected $valueConverter;

    /**
     * @param ProductValueConverter $valueConverter
     */
    public function __construct(ProductValueConverter $valueConverter)
    {
        $this->valueConverter = $valueConverter;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertField($field, $data, array $convertedItem, array $options)
    {
        switch ($field) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            case 'axis':
                $convertedItem[$field] = implode(',', $data);
                break;
            case 'values':
                foreach ($data as $valueField => $valueData) {
                    $convertedItem = array_merge(
                        $convertedItem,
                        $this->valueConverter->convertField($valueField, $valueData)
                    );
                }
                break;
            case 'code':
            case 'type':
            default:
                $convertedItem[$field] = (string) $data;
                break;
        }

        return $convertedItem;
    }
}
