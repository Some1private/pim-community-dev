<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

/**
 * Convert standard format to flat format for product
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Product extends AbstractSimpleArrayConverter implements ArrayConverterInterface
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
            case 'associations':
                $convertedItem = $this->convertAssociations($data, $convertedItem);
                break;
            case 'categories':
                $convertedItem[$field] = implode(',', $data);
                break;
            case 'enabled':
            case 'family':
                $convertedItem[$field] = (string) $data;
                break;
            case 'groups':
            case 'variant_group':
                $convertedItem = $this->convertGroups($data, $convertedItem);
                break;
            default:
                $convertedItem = array_merge(
                    $convertedItem,
                    $this->valueConverter->convertField($field, $data)
                );
                break;
        }

        return $convertedItem;
    }

    /**
     * Convert flat groups & variant_group to flat unified groups.
     *
     * @param mixed $data
     * @param array $convertedItem
     *
     * @return array
     */
    protected function convertGroups($data, array $convertedItem)
    {
        $groups = is_array($data) ? implode(',', $data) : (string) $data;

        if (isset($convertedItem['groups'])) {
            $convertedItem['groups'] .= sprintf(',%s', $groups);
        } else {
            $convertedItem['groups'] = $groups;
        }

        return $convertedItem;
    }

    /**
     * Convert flat formatted associations to standard ones.
     *
     * Given this $data:
     * [
     *     'UPSELL' => [
     *         'groups'   => [],
     *         'products' => []
     *     ],
     *     'X_SELL' => [
     *         'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
     *         'products' => ['akn_ts', 'oro_tsh']
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'UPSELL-groups'   => '',
     *     'UPSELL-products' => '',
     *     'X_SELL-groups'   => 'akeneo_tshirt,oro_tshirt',
     *     'X_SELL-products' => 'akn_ts,oro_tsh',
     * ]
     *
     * @param array $data
     * @param array $convertedItem
     *
     * @return array
     */
    protected function convertAssociations(array $data, array $convertedItem)
    {
        foreach ($data as $assocName => $associations) {
            foreach ($associations as $assocType => $entities) {
                $fieldName = sprintf('%s-%s', $assocName, $assocType);
                $convertedItem[$fieldName] = implode(',', $entities);
            }
        }

        return $convertedItem;
    }
}
