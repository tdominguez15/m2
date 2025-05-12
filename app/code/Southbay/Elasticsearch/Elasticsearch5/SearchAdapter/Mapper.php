<?php

namespace Southbay\Elasticsearch\Elasticsearch5\SearchAdapter;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as OriginalMapper;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use InvalidArgumentException;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\MatchQuery as MatchQueryBuilder;

class Mapper extends OriginalMapper
{
    /**
     * Sobrescribe processQuery para modificar la lógica y permitir busquedas multiples SKU
     *
     * @param RequestQueryInterface $requestQuery
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     * @throws InvalidArgumentException
     */
    protected function processQuery(
        RequestQueryInterface $requestQuery,
        array $selectQuery,
                              $conditionType
    ) {
        switch ($requestQuery->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                $value = $requestQuery->getValue();


                $value = trim($value); 
                $separatorPattern = '/[\s,]+/';
                $skus = preg_split($separatorPattern, $value);

                // Verifica si hemos encontrado más de un SKU
                if (count($skus) > 1) {
                    $skus = array_map('trim', $skus);
                    $boolQuery = ['bool' => ['must' => []]];
                    $boolQuery['bool']['must'][] = [
                        'terms' => ['visibility' => ['3', '4']]
                    ];
                    $boolQuery['bool']['must'][] = [
                        'terms' => ['sku.keyword' => $skus]
                    ];
                    $selectQuery = $boolQuery;
                } else {
                    return parent::processQuery($requestQuery, $selectQuery, $conditionType);
                }
                break;
            case RequestQueryInterface::TYPE_BOOL:
                $selectQuery = $this->processBoolQuery($requestQuery, $selectQuery);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                $selectQuery = $this->processFilterQuery($requestQuery, $selectQuery, $conditionType);
                break;
            default:
                throw new InvalidArgumentException(sprintf(
                    'Unknown query type \'%s\'',
                    $requestQuery->getType()
                ));
        }
        return $selectQuery;
    }

    /**
     * Procesa una consulta de tipo filtro.
     *
     * @param FilterQuery $query
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     */
    protected function processFilterQuery(
        FilterQuery $query,
        array $selectQuery,
                    $conditionType
    ) {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $selectQuery = $this->processQuery($query->getReference(), $selectQuery, $conditionType);
                break;
            case FilterQuery::REFERENCE_FILTER:
                $conditionType = $conditionType === BoolQuery::QUERY_CONDITION_NOT
                    ? MatchQueryBuilder::QUERY_CONDITION_MUST_NOT
                    : $conditionType;
                $filterQuery = $this->filterBuilder->build($query->getReference(), $conditionType);
                foreach ($filterQuery['bool'] as $condition => $filter) {
                    $selectQuery['bool'][$condition] = array_merge(
                        $selectQuery['bool'][$condition] ?? [],
                        $filter
                    );
                }
                break;
        }

        return $selectQuery;
    }
}
