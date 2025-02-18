# Customize your search

> [!IMPORTANT]
> If you haven't used the maker, you first need to create a class and add the `AsSearch` attribute to it.

**Create a search**
```php
<?php

declare(strict_types=1);

namespace App\Search;

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;

#[AsSearch('products')]
class ListingSearch extends AbstractSearch
{
}
```

**Now you can add plenty of features to your search.**

## Add facets

If you wish, you can add facets to your Search. To do this, you need to use the `addFacet` method.
This method takes the following parameters:

| Parameter        | Description                 | Type   | Required |
|------------------|-----------------------------|--------|----------|
| property         | Property name               | string | ✅        |
| label            | Label displayed             | string | ✅        |
| displayComponent | FQCN of your Twig component | string | ❌        |
| props            | Props to pass to component  | array  | ❌        |


```php
<?php

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\TestApplication\Search;

use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Twig\Component\Facet\RangeInput;

#[AsSearch('products', name: 'listing', adapter: 'meilisearch')]
class MeilisearchSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addFacet('type', 'Type')
            ->addFacet('price', 'Price', RangeInput::class)
            ->addFacet('price', 'Price', null, ['limit' => 20])
        ;
    }
}
```

## Add sort

You can also add sorting options to your Search. To do this, you need to use the `addAvailableSort` method.
This method takes 2 mandatory parameters:

| Parameter | Description                             | Type    |
|-----------|-----------------------------------------|---------|
| key       | Attribute key and order separate by ':' | ?string |
| label     | Label displayed                         | string  |

If you do not specify a sort or if the key is empty, the default sorting of your adapter will be applied.

```php
    use Mezcalito\UxSearchBundle\Search\Sort;
    
    // ..
    
    public function build(array $options = []): void
    {
        $this
            // ..
            ->addAvailableSort(null, 'Relevancy') 
            ->addAvailableSort('price:asc', 'Price ↑')
            ->addAvailableSort('price:desc', 'Price ↓')
        ;
    }
```

## Add EventListener or EventSubscriber
For example, you can modify the `ResultSet` on the `PostSearchEvent` to enrich a `Hit` with data from database.

```php
    use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
    use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
    // ..
    
    public function build(array $options = []): void
    {
        $this 
            // ...
            ->addEventListener(PreSearchEvent::class, function (PreSearchEvent $event) {
                // $event->getSearch();   
                // $event->getQuery();   
            })
            ->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
                // $event->getSearch();
                // $event->getQuery();
                // $event->getResultSet();    
           })
           ->addEventSubscriber(YourEventSubscriber::cass)
        ;
    }
```

## Enable urlRewriting and set up an urlFormater
It is possible to enable a URL rewriting system to allow sharing of configured search URLs. To do this, simply add the `->enableUrlRewriting` method. By default, a `DefaultUrlFormater` is provided in the bundle. This allows you to add query parameters with the values of the selected facets.

```php

    public function build(array $options = []): void
    {
        $this
            // ...
            ->enableUrlRewriting()
        ;
    }
```

You can also create your own UrlFormater. To do so, you need to implement the `UrlFormaterInterface` and define your own logic in the `generateUrl` and `applyFilters` methods. All that is left is to use it in a search via the `->setUrlFormater()` method.

```php
    use App\Url\YourCustomUrlFormater;
    // ...

    public function build(array $options = []): void
    {
        $this
            // ...
            ->enableUrlRewriting()
            ->setUrlFormater(YourCustomUrlFormater::class)
        ;
    }
```
