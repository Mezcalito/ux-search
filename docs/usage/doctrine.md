# Doctrine Adapter

The Doctrine adapter allows you to perform searches directly on your database using Doctrine ORM. It's ideal for development, prototyping, or applications with small to medium-sized datasets.

## When to use Doctrine Adapter

**Best for:**
- Development and testing environments
- Small to medium catalogs (< 10,000 items)
- Applications already using Doctrine ORM
- No need for external search infrastructure

**Consider alternatives (Algolia/Meilisearch) for:**
- Large datasets (> 10,000 items)
- Advanced features like typo tolerance
- Sub-second search on large catalogs
- Heavy traffic production environments

## Configuration Required

### SEARCH_FIELDS (Required)

The `SEARCH_FIELDS` parameter is **required** for search to work. It defines which entity fields should be searched when the user types a query.

```php
DoctrineAdapter::SEARCH_FIELDS => ['o.name', 'o.brand', 'o.description']
```

**Important notes:**
- Use DQL syntax with the query builder alias (e.g., `o.name`, not just `name`)
- Multiple fields are combined with OR logic
- Full-text search is performed with `LIKE %query%`
- Searching across relations is supported (e.g., `o.category.name`)

### QUERY_BUILDER_ALIAS

Defines the alias used for the main entity in the query builder. Default is `'o'`.

```php
DoctrineAdapter::QUERY_BUILDER_ALIAS => 'p' // Use 'p' instead of 'o'
```

Make sure your `SEARCH_FIELDS` use the same alias.

### QUERY_BUILDER

A closure that allows you to customize the base query. Useful for:
- Adding WHERE conditions (filtering out inactive items)
- Adding JOINs (for related entities)
- Adding default ordering
- Any other QueryBuilder modifications

```php
DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) {
    $qb->andWhere('o.active = :active')
       ->setParameter('active', true);
}
```

### MAX_FACET_VALUES_PARAM

Maximum number of distinct values to return for each facet. Default is 100.

```php
DoctrineAdapter::MAX_FACET_VALUES_PARAM => 50
```

## Available Configuration Parameters

| Constant name          | Type     | Default value                              | Description                               |
|------------------------|----------|--------------------------------------------|-------------------------------------------|
| MAX_FACET_VALUES_PARAM | int      | 100                                        | Maximum number of facet values per facet  |
| QUERY_BUILDER_ALIAS    | string   | o                                          | Alias for the main entity in DQL queries  |
| QUERY_BUILDER          | closure  | `function (QueryBuilder $queryBuilder) {}` | Customize the base query                  |
| SEARCH_FIELDS          | string[] | []                                         | **Required** - Entity fields to search in |

## Complete Example

Here's a complete Search class using the Doctrine adapter:

```php
<?php

namespace App\Search;

use Doctrine\ORM\QueryBuilder;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;
use App\Entity\Product;

#[AsSearch(Product::class, adapter: 'doctrine')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                DoctrineAdapter::SEARCH_FIELDS => ['o.name', 'o.brand', 'o.description'],
                DoctrineAdapter::QUERY_BUILDER_ALIAS => 'o',
                DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) {
                    // Only show active products
                    $qb->andWhere('o.active = :active')
                       ->setParameter('active', true);
                },
                DoctrineAdapter::MAX_FACET_VALUES_PARAM => 30,
            ])
            ->addFacet('o.category', 'Category')
            ->addFacet('o.brand', 'Brand')
            ->addFacet('o.price', 'Price', RangeInput::class)
            ->addAvailableSort('o.price:asc', 'Price ↑')
            ->addAvailableSort('o.price:desc', 'Price ↓')
            ->addAvailableSort('o.createdAt:desc', 'Newest')
            ->setAvailableHitsPerPage([12, 24, 48])
        ;
    }
}
```

## Advanced Examples

### Searching with Relationships

You can search in related entity fields using joins:

```php
->setAdapterParameters([
    DoctrineAdapter::SEARCH_FIELDS => [
        'p.name',
        'p.description',
        'c.name',  // Search in category name
        'b.name',  // Search in brand name
    ],
    DoctrineAdapter::QUERY_BUILDER_ALIAS => 'p',
    DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) {
        $qb->leftJoin('p.category', 'c')
           ->leftJoin('p.brand', 'b')
           ->andWhere('p.deletedAt IS NULL');
    },
])
```

### Filtering by User Context

```php
public function __construct(private Security $security)
{
}

public function build(array $options = []): void
{
    $user = $this->security->getUser();

    $this->setAdapterParameters([
        DoctrineAdapter::SEARCH_FIELDS => ['o.title', 'o.description'],
        DoctrineAdapter::QUERY_BUILDER_ALIAS => 'o',
        DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) use ($user) {
            // Show only items accessible by this user
            $qb->andWhere('o.visibility = :public OR o.owner = :user')
               ->setParameter('public', 'public')
               ->setParameter('user', $user);
        },
    ]);
}
```

## Performance Considerations

### Indexing

For better performance, add database indexes on:
- Fields used in `SEARCH_FIELDS`
- Fields used in facets
- Fields used in sorting

```sql
CREATE INDEX idx_product_name ON product(name);
CREATE INDEX idx_product_brand ON product(brand);
CREATE INDEX idx_product_price ON product(price);
```

### Limitations

The Doctrine adapter has some limitations compared to dedicated search engines:

| Feature            | Doctrine                | Algolia/Meilisearch |
|--------------------|-------------------------|---------------------|
| **Typo tolerance** | ❌ None                  | ✅ Built-in          |
| **Highlighting**   | ⚠️ Basic                | ✅ Advanced          |
| **Faceting**       | ⚠️ Calculated on-demand | ✅ Pre-calculated    |
| **Performance**    | ⚠️ Depends on DB size   | ✅ Consistently fast |
| **Relevance**      | ⚠️ Basic LIKE matching  | ✅ Advanced ranking  |

### Optimization Tips

1. **Limit dataset size**: Use `QUERY_BUILDER` to filter out unnecessary items
2. **Use pagination**: Don't retrieve all results at once
3. **Reduce facet count**: Limit `MAX_FACET_VALUES_PARAM` to reasonable number
4. **Add indexes**: Index all searchable and sortable fields
5. **Consider caching**: Cache facet values if they don't change often

## Troubleshooting

### No results returned

**Check:**
- `SEARCH_FIELDS` is not empty
- Field names use correct DQL syntax with alias (e.g., `o.name`)
- The query builder alias matches between `QUERY_BUILDER_ALIAS` and `SEARCH_FIELDS`

### Slow queries

**Solutions:**
- Add database indexes on search fields
- Reduce the number of search fields
- Limit the dataset with `QUERY_BUILDER` conditions
- Consider switching to Algolia or Meilisearch for larger datasets

### Facets not working

**Check:**
- Facet property uses correct DQL syntax (e.g., `o.category` not just `category`)
- The field contains values (not all NULL)
- `MAX_FACET_VALUES_PARAM` is not set too low

## Reference Implementation

See the complete working example in the test application:
[tests/TestApplication/src/Search/DoctrineSearch.php](../../tests/TestApplication/src/Search/DoctrineSearch.php)
