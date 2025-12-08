# Layout Component

The `Layout` component is the root wrapper component that orchestrates all search UI components. It provides the overall structure and default placement for search input, facets, results, and pagination.

## Usage

```twig
<twig:Mezcalito:UxSearch:Layout name="products" />
```

## Available Variables

| Variable     | Type                | Description                                              |
|--------------|---------------------|----------------------------------------------------------|
| `search`     | Search              | The search configuration object with facets, sorts, etc. |
| `attributes` | ComponentAttributes | HTML attributes for the root container                   |

## Blocks Available

| Block Name   | Description                                                                               |
|--------------|-------------------------------------------------------------------------------------------|
| `content`    | Main block wrapping all search UI - override to completely restructure the search layout  |
| `form`       | Contains the search input field - override to customize or add additional search controls |
| `toolbar`    | Contains refinements display, clear button, pagination controls, and sort options         |
| `facets`     | Contains all facet components - override to customize facet sidebar layout                |
| `listing`    | Wraps stats, hits, and pagination - the main results area                                 |
| `stats`      | Contains the total hits counter - override to add additional statistics                   |
| `hits`       | Contains the search results display - override to change results positioning              |
| `pagination` | Contains pagination controls - override to reposition or customize pagination             |

## Default Layout

```twig
<div {{ attributes.defaults({
    'class': 'ux-search',
    'data-controller': 'ux-search',
    'data-loading': 'addClass(ux-search--is-loading)'
}) }}>
    {% block content %}
        <div class="ux-search__inner">
            <div class="ux-search__form">
                {% block form %}
                    <twig:Mezcalito:UxSearch:SearchInput />
                {% endblock %}
            </div>

            <div class="ux-search__toolbar">
                {% block toolbar %}
                    <twig:Mezcalito:UxSearch:CurrentRefinements/>
                    <twig:Mezcalito:UxSearch:ClearRefinements />
                    <twig:Mezcalito:UxSearch:HitsPerPage/>
                    <twig:Mezcalito:UxSearch:SortBy/>
                {% endblock %}
            </div>

            <div class="ux-search__facets">
                {% block facets %}
                    {% for facet in search.facets %}
                        <twig:Mezcalito:UxSearch:Facet :property="facet.property" />
                    {% endfor %}
                {% endblock %}
            </div>

            <div class="ux-search__listing">
                {% block listing %}
                    <div class="ux-search__stats">
                        {% block stats %}
                            <twig:Mezcalito:UxSearch:TotalHits/>
                        {% endblock %}
                    </div>

                    {% block hits %}
                        <twig:Mezcalito:UxSearch:Hits />
                    {% endblock %}

                    {% block pagination %}
                        <twig:Mezcalito:UxSearch:Pagination />
                    {% endblock %}
                {% endblock %}
            </div>
        </div>
    {% endblock %}
</div>
```

## Data Attributes

The root element includes Stimulus controller attributes:
- `data-controller="ux-search"` - Activates the search Stimulus controller
- `data-loading="addClass(ux-search--is-loading)"` - Adds loading class during search updates

## Styling

Default classes:
- `.ux-search` - Root container with Stimulus controller
- `.ux-search__inner` - Inner wrapper for layout structure
- `.ux-search__form` - Search input section
- `.ux-search__toolbar` - Toolbar with filters and controls
- `.ux-search__facets` - Facets sidebar/section
- `.ux-search__listing` - Main results area
- `.ux-search__stats` - Statistics section (total hits)
- `.ux-search--is-loading` - Applied during search updates

## Related Components

All components are included within Layout:
- [SearchInput](SearchInput.md) - Search query input
- [CurrentRefinements](CurrentRefinements.md) - Active filter badges
- [ClearRefinements](ClearRefinements.md) - Clear all filters button
- [HitsPerPage](HitsPerPage.md) - Results per page selector
- [SortBy](SortBy.md) - Sort order dropdown
- [Facet/RefinementList](Facet/RefinementList.md) - Term facets
- [Facet/RangeSlider](Facet/RangeSlider.md) - Range sliders
- [Facet/RangeInput](Facet/RangeInput.md) - Range inputs
- [TotalHits](TotalHits.md) - Result count display
- [Hits](Hits.md) - Search results list
- [Pagination](Pagination.md) - Page navigation
