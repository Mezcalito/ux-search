# Doctrine

## Available configuration for adapter

| Constant name          | type     | default value                              |
|------------------------|----------|--------------------------------------------|
| MAX_FACET_VALUES_PARAM | int      | 100                                        |
| QUERY_BUILDER_ALIAS    | string   | o                                          |
| QUERY_BUILDER          | closure  | `function (QueryBuilder $queryBuilder) {}` |
| SEARCH_FIELDS          | string[] | []                                         |
