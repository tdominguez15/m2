<?php

namespace Southbay\Elasticsearch\Plugin;

use Magento\CatalogSearch\Block\Result;

class MinimizeSearchQueryText
{
    /**
     * Modify the search query text to truncate it if it exceeds 200 characters.
     *
     * @param Result $subject
     * @param string $result
     * @return string
     */
    public function afterGetSearchQueryText(Result $subject, $result)
    {
        $maxLength = 200;
        if (strlen($result) > $maxLength) {
            return substr($result, 0, $maxLength) . '...';
        }

        return $result;
    }
}
