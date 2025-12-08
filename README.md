# Mezcalito UX Search

[![Latest Version](https://img.shields.io/packagist/v/mezcalito/ux-search.svg)](https://packagist.org/packages/mezcalito/ux-search)
[![License](https://img.shields.io/packagist/l/mezcalito/ux-search.svg)](https://github.com/mezcalito/ux-search/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/mezcalito/ux-search.svg)](https://packagist.org/packages/mezcalito/ux-search)

A powerful, flexible, and easy-to-use search and faceted search system for Symfony applications, built with Twig Components and Live Components.

[![Effortless search and faceted search with Symfony UX and Mezcalito UX Search](docs/image/preview.png)](https://ux-search.mezcalito.dev/)

**[View Live Demo](https://ux-search.mezcalito.dev/demo)** | **[Documentation](docs/)** | **[Report Issues](https://github.com/mezcalito/ux-search/issues)**

---

## Why Use This Bundle?

- **🚀 Quick Setup**: Get a working search in minutes with the maker command
- **🔌 Multiple Adapters**: Support for Algolia, Meilisearch, and Doctrine ORM
- **🎨 Fully Customizable**: Override templates and components to match your design
- **⚡ Live Updates**: Built with Symfony UX Live Components for reactive UI
- **🎯 Faceted Search**: Rich filtering with refinement lists, range sliders, and more
- **📦 Production Ready**: Used in production with comprehensive test coverage

## Features

- **Multiple Search Configurations**: Create and manage multiple searches, each with its own unique configuration
- **Flexible Adapters**:
  - **Algolia**: Cloud-based search with advanced features
  - **Meilisearch**: Self-hosted open-source search engine
  - **Doctrine ORM**: Use your existing database for small datasets
- **Rich UI Components**: Pre-built components for search input, facets, pagination, sorting, and more
- **Faceted Navigation**: Multiple facet types (refinement lists, range inputs, range sliders)
- **Live Components**: Real-time updates without page reloads
- **Event System**: Customize search behavior with pre/post search events
- **SEO Friendly**: URL rewriting support for search parameters
- **Customizable**: Override any template or extend any component

## Requirements

- PHP 8.3 or higher
- Symfony 6.4+ or 7.0+ or 8.0+
- Symfony UX (Live Components, Twig Components)


## Installation

Install the bundle via Composer:

```bash
composer require mezcalito/ux-search
```

If you're **not** using Symfony Flex, you'll need to manually register the bundle in `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...
    Mezcalito\UxSearchBundle\MezcalitoUxSearchBundle::class => ['all' => true],
];
```

## Quick Start

### 1. Configure an Adapter

Create a configuration file `config/packages/mezcalito_ux_search.yaml`:

```yaml
mezcalito_ux_search:
    default_adapter: 'default'
    adapters:
        default: '%env(MEZCALITO_UX_SEARCH_DEFAULT_DSN)%'
```

Add the DSN to your `.env` file (choose one):

```bash
# For Algolia
MEZCALITO_UX_SEARCH_DEFAULT_DSN=algolia://YOUR_API_KEY@YOUR_APP_ID

# For Meilisearch
MEZCALITO_UX_SEARCH_DEFAULT_DSN=meilisearch://YOUR_MASTER_KEY@localhost:7700

# For Doctrine ORM
MEZCALITO_UX_SEARCH_DEFAULT_DSN=doctrine://default
```

### 2. Create Your First Search

Use the maker command to generate a search class:

```bash
php bin/console make:search
```

The command will ask you for:
- **Index name**: For Algolia/Meilisearch, the index name. For Doctrine, the entity FQCN (e.g., `App\Entity\Product`)
- **Search name** (optional): Custom name for your search (defaults to class name without "Search" suffix)
- **Adapter** (optional): Which adapter to use (defaults to `default_adapter`)

This creates a search class in `src/Search/` that you can customize.

### 3. Render the Search in Your Template

In any Twig template:

```twig
{# Using Twig component syntax #}
<twig:Mezcalito:UxSearch:Layout name="product"/>

{# Or using component function #}
{{ component('Mezcalito:UxSearch:Layout', { name: 'product' }) }}
```

That's it! You now have a working search with facets, pagination, and live updates. 🎉

## Choosing an Adapter

Three adapters are available, each with different strengths:

| Adapter         | Best For                    | Performance | Cost    | Setup Complexity |
|-----------------|-----------------------------|-------------|---------|------------------|
| **Algolia**     | Production, large datasets  | ⭐⭐⭐         | 💰 Paid | Easy             |
| **Meilisearch** | Self-hosted production      | ⭐⭐⭐         | 🆓 Free | Medium           |
| **Doctrine**    | Development, small datasets | ⭐⭐          | 🆓 Free | Very Easy        |

### Adapter DSN Format

| Adapter     | DSN Format                     | Documentation                          |
|-------------|--------------------------------|----------------------------------------|
| Algolia     | `algolia://apiKey@appId`       | [View docs](docs/usage/algolia.md)     |
| Meilisearch | `meilisearch://key@host:port`  | [View docs](docs/usage/meilisearch.md) |
| Doctrine    | `doctrine://entityManagerName` | [View docs](docs/usage/doctrine.md)    |

**Need another provider?** You can [create your own adapter](docs/create-own-adapter.md).

## Customizing Your Search

### Adding Facets, Sorting, and More

Once you've created a search class, customize it by editing the `build()` method:

```php
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;

#[AsSearch(index: 'products', adapter: 'default')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        // Add facets for filtering
        $this->addFacet('brand', 'Brand');
        $this->addFacet('category', 'Category');
        $this->addFacet('price', 'Price', RangeInput::class);

        // Add sorting options
        $this->addAvailableSort('name', 'Name');
        $this->addAvailableSort('price', 'Price');
        $this->addAvailableSort('created_at', 'Newest');

        // Configure pagination
        $this->setAvailableHitsPerPage([12, 24, 48]);

        // Adapter-specific parameters
        $this->setAdapterParameters([
            // Adapter-specific options here
        ]);
    }
}
```

📖 **[Full customization guide](docs/usage/customize-your-search.md)**


### Customizing the UI

The bundle provides a complete set of UI components that you can use individually or override:

#### Core Components

| Component       | Description                                             | Documentation                          |
|-----------------|---------------------------------------------------------|----------------------------------------|
| **Layout**      | Root wrapper component containing all search elements   | [Docs](docs/components/Layout.md)      |
| **SearchInput** | Text search input with live updates                     | [Docs](docs/components/SearchInput.md) |
| **Hits**        | Display search results with customizable item templates | [Docs](docs/components/Hits.md)        |
| **Pagination**  | Navigate through search results                         | [Docs](docs/components/Pagination.md)  |

#### Facet Components

| Component          | Description                                   | Documentation                                   |
|--------------------|-----------------------------------------------|-------------------------------------------------|
| **RefinementList** | Checkbox/radio list for categorical filtering | [Docs](docs/components/Facet/RefinementList.md) |
| **RangeInput**     | Min/max input fields for numeric ranges       | [Docs](docs/components/Facet/RangeInput.md)     |
| **RangeSlider**    | Slider for numeric range filtering            | [Docs](docs/components/Facet/RangeSlider.md)    |

#### Utility Components

| Component              | Description                                | Documentation                                 |
|------------------------|--------------------------------------------|-----------------------------------------------|
| **CurrentRefinements** | Display active filters with remove buttons | [Docs](docs/components/CurrentRefinements.md) |
| **ClearRefinements**   | Button to clear all active filters         | [Docs](docs/components/ClearRefinements.md)   |
| **SortBy**             | Dropdown to change sort order              | [Docs](docs/components/SortBy.md)             |
| **TotalHits**          | Display total number of results            | [Docs](docs/components/TotalHits.md)          |

### Overriding Templates

You can override any component template by creating a file in your app's `templates/` directory:

```
templates/
└── components/
    └── Mezcalito/
        └── UxSearch/
            ├── Layout.html.twig          # Override the main layout
            ├── SearchInput.html.twig     # Override search input
            ├── Hits.html.twig            # Override results display
            └── Facet/
                └── RefinementList.html.twig
```

### Custom Hit Template

The most common customization is the hit (result item) template. Override `Hits.html.twig`:

```twig
{# templates/components/Mezcalito/UxSearch/Hits.html.twig #}
<div {{ attributes }}>
    {% for hit in this.resultSet.hits %}
        <article class="product-card">
            <img src="{{ hit.image }}" alt="{{ hit.name }}">
            <h3>{{ hit.name }}</h3>
            <p class="price">{{ hit.price|format_currency('EUR') }}</p>
            <a href="{{ path('product_show', {id: hit.id}) }}">View details</a>
        </article>
    {% endfor %}
</div>
```


## Advanced Usage

### Event System

Customize search behavior with event subscribers:

```php
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PreSearchEvent::class => 'onPreSearch',
        ];
    }

    public function onPreSearch(PreSearchEvent $event): void
    {
        $query = $event->getQuery();
        // Modify the query before search execution
        $query->addFilter('status', 'published');
    }
}
```

📖 **[Event system documentation](docs/usage/customize-your-search.md#event-subscribers)**

### Multiple Search Configurations

You can have multiple search configurations in one application:

```php
// Product search with Algolia
#[AsSearch(index: 'products', adapter: 'algolia')]
class ProductSearch extends AbstractSearch { }

// Blog search with Meilisearch
#[AsSearch(index: 'posts', adapter: 'meilisearch')]
class BlogSearch extends AbstractSearch { }

// User search with Doctrine
#[AsSearch(index: 'App\Entity\User', adapter: 'orm')]
class UserSearch extends AbstractSearch { }
```

Each search can have its own adapter, facets, and configuration.

## Documentation

### Getting Started
- [Installation & Quick Start](#installation)
- [Choosing an Adapter](#choosing-an-adapter)
- [Customizing Your Search](#customizing-your-search)

### Adapters
- [Algolia Configuration](docs/usage/algolia.md)
- [Meilisearch Configuration](docs/usage/meilisearch.md)
- [Doctrine Configuration](docs/usage/doctrine.md)
- [Creating a Custom Adapter](docs/create-own-adapter.md)

### Components
- [Layout](docs/components/Layout.md) - Root wrapper
- [SearchInput](docs/components/SearchInput.md) - Search box
- [Hits](docs/components/Hits.md) - Results display
- [Pagination](docs/components/Pagination.md) - Page navigation
- [Facets](docs/components/) - All facet components
- [View all components](docs/components/)

### Advanced
- [Customizing Your Search](docs/usage/customize-your-search.md) - Facets, sorting, events
- [Component Customization](docs/components/) - Override templates and behavior

## Contributing

Contributions are welcome! Here's how you can help:

1. **Report bugs** - [Open an issue](https://github.com/mezcalito/ux-search/issues) with a clear description
2. **Request features** - [Suggest new features](https://github.com/mezcalito/ux-search/issues) with use cases
3. **Submit PRs** - Fork, create a feature branch, and submit a pull request
4. **Improve docs** - Documentation improvements are always appreciated

### Development Setup

```bash
# Clone the repository
git clone https://github.com/mezcalito/ux-search.git
cd ux-search

# Start the Docker development environment
make up

# Install dependencies
make install

# Run tests
make test

# Run code quality checks
make ci
```

## Support

- **Issues**: [GitHub Issues](https://github.com/mezcalito/ux-search/issues)
- **Discussions**: [GitHub Discussions](https://github.com/mezcalito/ux-search/discussions)
- **Demo**: [Live Demo](https://ux-search.mezcalito.dev/demo)

## License

This bundle is released under the [MIT License](LICENSE).

