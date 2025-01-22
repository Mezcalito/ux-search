# Create own Adapter

In this part of the documentation describes how to create an own adapter. Before you start with it let us know via an [issue](https://github.com/mezcalito/ux-search/issues) if it maybe an Adapter which make sense to add to the project, and we can work together to get it in it.

## Create Basic Classes

### Create Adapter

```php
<?php

declare(strict_types=1);

namespace My\Own\Adapter;

use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class MyAdapter implements AdapterInterface
{
    
    public function search(Query $query, SearchInterface $search): ResultSet
    {
        // Your logic
    }

    public function configureParameters(OptionsResolver $resolver): void
    {
        // Your logic
    }
}
```

### Create Factory

```php
<?php

declare(strict_types=1);

namespace My\Own\Adapter;

use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;

readonly class MyAdapterFactory implements AdapterFactoryInterface
{
    public function support(string $dsn): bool
    {
        return str_starts_with($dsn, 'myAdapter'); // Or your own logic
    }

    public function createAdapter(string $dsn): AdapterInterface
    {
        // Your own logic

        return new YourAdapter();
    }
}
```

## And "Et voilà"

You just need to add your Adapter to the configuration. 

```yaml
mezcalito_search:
    default_adapter: 'myAdapter'
    adapter:
        myAdapter: 'myAdapter://what_you_need',
```


