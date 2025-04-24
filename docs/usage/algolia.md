# Algolia

## Available configuration for adapter

| Constant name                              | Algolia name                     | Type     | Default value                          |
|--------------------------------------------|----------------------------------|----------|----------------------------------------|
| ADVANCED_SYNTAX_PARAM                      | advancedSyntax                   | bool     | false                                  |
| ADVANCED_SYNTAX_FEATURES_PARAM             | advancedSyntaxFeatures           | string[] | ['exactPhrase', 'excludeWords']        |
| ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM        | allowTyposOnNumericTokens        | bool     | true                                   |
| ALTERNATIVES_AS_EXACT_PARAM                | alternativesAsExact              | string[] | ['ignorePlurals', 'singleWordSynonym'] |
| ANALYTICS_PARAM                            | analytics                        | bool     | true                                   |
| ANALYTICS_TAGS_PARAM                       | analyticsTags                    | string[] | []                                     |
| ATTRIBUTES_TO_HIGHLIGHT_PARAM              | attributesToHighlight            | string[] | ['*']                                  |
| ATTRIBUTES_TO_RETRIEVE_PARAM               | attributesToRetrieve             | string[] | ['*']                                  |
| DECOMPOUND_QUERY_PARAM                     | decompoundQuery                  | bool     | true                                   |
| DISABLE_EXACT_ON_ATTRIBUTES_PARAM          | disableExactOnAttributes         | string[] | []                                     |
| DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM | disableTypoToleranceOnAttributes | string[] | []                                     |
| ENABLE_AB_TEST_PARAM                       | enableABTest                     | bool     | true                                   |
| ENABLE_PERSONALIZATION_PARAM               | enablePersonalization            | bool     | false                                  |
| HIGHLIGHT_POST_TAG_PARAM                   | highlightPostTag                 | string   | &lt;/em&gt;                            |
| HIGHLIGHT_PRE_TAG_PARAM                    | highlightPreTag                  | string   | &lt;em&gt;                             |
| MAX_VALUES_PER_FACET_PARAM                 | maxValuesPerFacet                | int      | 100                                    |
| QUERY_TYPE_PARAM                           | queryType                        | string   | prefixLast                             |
| SORT_FACET_VALUES_BY_PARAM                 | sortFacetValuesBy                | string   | count                                  |
| SYNONYMS_PARAM                             | synonyms                         | bool     | true                                   |

If you need more inforamtion about this configuration check [Algolia documentation](https://www.meilisearch.com/docs/reference/api/search#body)


