<?php


namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert standard format to flat format
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($field, $data, $convertedItem, $options);
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param mixed  $data
     * @param array  $convertedItem
     * @param array  $options
     *
     * @return array the converted item
     */
    abstract protected function convertProperty($field, $data, array $convertedItem, array $options);
}
