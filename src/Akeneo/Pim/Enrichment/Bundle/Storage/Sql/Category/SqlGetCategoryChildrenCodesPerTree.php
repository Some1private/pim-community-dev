<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Pim\Enrichment\Bundle\Filter\CategoryCodeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\GetCategoryChildrenCodesPerTreeInterface;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetCategoryChildrenCodesPerTree implements GetCategoryChildrenCodesPerTreeInterface
{
    private Connection $connection;
    private CategoryCodeFilterInterface $categoryCodeFilter;

    public function __construct(Connection $connection, CategoryCodeFilterInterface $categoryCodeFilter)
    {
        $this->connection = $connection;
        $this->categoryCodeFilter = $categoryCodeFilter;
    }

    public function executeWithChildren(array $selectedCategoryCodes): array
    {
        Assert::allStringNotEmpty($selectedCategoryCodes);

        $query = <<<SQL
WITH categoriesSelectedByTreeCount (id, code, childrenCodes) AS (
    SELECT root.id as id, root.code as code, JSON_ARRAYAGG(child.code)
    FROM pim_catalog_category parent
             JOIN pim_catalog_category child
                  ON child.lft >= parent.lft AND child.lft < parent.rgt AND child.root = parent.root
             JOIN pim_catalog_category root
                  ON root.id = child.root
    WHERE parent.code IN (:selectedCategories)
    GROUP BY root.id
)
SELECT c.code, COALESCE(categoriesSelectedByTreeCount.childrenCodes, '[]') AS children_codes
FROM pim_catalog_category c
    LEFT JOIN categoriesSelectedByTreeCount
        ON categoriesSelectedByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategoryCodes],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $childrenCodes = array_unique(json_decode($result['children_codes'], true));

            $results[$result['code']] = $this->categoryCodeFilter->filter($childrenCodes);
        }

        return $results;
    }

    public function executeWithoutChildren(array $selectedCategoryCodes): array
    {
        Assert::allStringNotEmpty($selectedCategoryCodes);

        $query = <<<SQL
WITH categoriesSelectedByTreeCount (id, childrenCodes) AS (
    SELECT
           root.id as id,
           JSON_ARRAYAGG(child.code)
    FROM pim_catalog_category child
        JOIN pim_catalog_category root
            ON child.root = root.id
    WHERE child.code IN (:selectedCategories)
    GROUP BY child.root
)
SELECT c.code, COALESCE(categoriesSelectedByTreeCount.childrenCodes, '[]') AS children_codes
FROM pim_catalog_category c
    LEFT JOIN categoriesSelectedByTreeCount
        ON categoriesSelectedByTreeCount.id = c.id
WHERE c.parent_id IS NULL;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            ['selectedCategories' => $selectedCategoryCodes],
            ['selectedCategories' => Connection::PARAM_STR_ARRAY]
        );

        $results = [];
        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $childrenCodes = array_unique(json_decode($result['children_codes'], true));

            $results[$result['code']] = $this->categoryCodeFilter->filter($childrenCodes);
        }

        return $results;
    }
}
