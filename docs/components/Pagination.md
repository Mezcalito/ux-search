# Pagination Component

The `Pagination` component displays a pagination system which lets users navigate through pages of search results. It includes intelligent ellipsis for large page counts and previous/next navigation.

## Usage

```twig
<twig:Mezcalito:UxSearch:Pagination />
```

## Available Variables

| Variable     | Type                | Description                                                                    |
|--------------|---------------------|--------------------------------------------------------------------------------|
| `page`       | int                 | Current page number (1-indexed)                                                |
| `totalPage`  | int                 | Total number of pages available                                                |
| `range`      | int                 | Number of pages to show on each side of current page                           |
| `pages`      | list<int\|null>     | Precomputed list of pages to render, where `null` marks an ellipsis            |
| `startRange` | int                 | Starting page number of visible range (still exposed, not used by the default template) |
| `endRange`   | int                 | Ending page number of visible range (still exposed, not used by the default template)   |
| `attributes` | ComponentAttributes | HTML attributes for the container                                              |

The component also exposes a helper method callable from Twig:

| Method              | Description                                                                                                         |
|---------------------|---------------------------------------------------------------------------------------------------------------------|
| `this.pageUrl(page)` | Returns the URL for the given page. When `enableUrlRewriting()` is active on the search, the URL formater generates a full URL preserving the current query, filters and sort; otherwise it falls back to `?page=N` |

## Blocks Available

| Block Name | Description                                                                             |
|------------|-----------------------------------------------------------------------------------------|
| `content`  | Main block wrapping the entire pagination navigation - override to change the structure |

## Default Layout

```twig
{%- block content %}
    {%- if totalPage > 1 %}
        <nav {{ attributes.defaults({'class': 'ux-search-pagination'}) }} >
            <ul class="ux-search-pagination__list">
                {% if (page - 1) >= 1 %}
                    <li class="ux-search-pagination__item">
                        <a
                            class="ux-search-pagination__link"
                            href="{{ this.pageUrl(page - 1) }}"
                            data-action="live#action:prevent"
                            data-live-action-param="changeCurrentPage"
                            data-live-page-param="{{ page - 1 }}"
                            rel="prev"
                        >
                            <span class="ux-search-sr-only">{{ 'pagination.previous_page'|trans(domain='mezcalito_ux_search') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                    </li>
                {% endif %}

                {% for p in pages %}
                    <li class="ux-search-pagination__item">
                        {% if p is null %}
                            {{ _self.elipsis() }}
                        {% else %}
                            {{ _self.link(p, page, this.pageUrl(p)) }}
                        {% endif %}
                    </li>
                {% endfor %}

                {% if page < totalPage %}
                    <li class="ux-search-pagination__item">
                        <a
                            class="ux-search-pagination__link{{ page < totalPage ? '' : ' is-disabled'}}"
                            href="{{ this.pageUrl(page + 1) }}"
                            data-action="live#action:prevent"
                            data-live-action-param="changeCurrentPage"
                            data-live-page-param="{{ page + 1 }}"
                            rel="next"
                        >
                            <span class="ux-search-sr-only">{{ 'pagination.next_page'|trans(domain='mezcalito_ux_search') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </li>
                {% endif %}
            </ul>
        </nav>
    {% endif -%}

    {%- macro link(iterator, page, url) %}
        {%- if page == iterator %}
            <span class="ux-search-pagination__link is-current">{{ iterator }}</span>
        {% else %}
            <a
                class="ux-search-pagination__link"
                href="{{ url|default('?page=' ~ iterator) }}"
                data-action="live#action:prevent"
                data-live-action-param="changeCurrentPage"
                data-live-page-param="{{ iterator }}"
            >
                {{ iterator }}
            </a>
        {% endif -%}
    {% endmacro -%}

    {%- macro elipsis() %}
        <span class="ux-search-pagination__link ux-search-pagination__ellipsis">...</span>
    {% endmacro -%}
{% endblock -%}
```

## Default HTML Output

When URL rewriting is disabled, links use the `?page=N` fallback shown below. When `enableUrlRewriting()` is active on the search, each `href` contains the full rewritten URL preserving the current query, filters and sort (e.g. `?query=laptop&brand=Dell&page=6`).

```html
<nav class="ux-search-pagination">
    <ul class="ux-search-pagination__list">
        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=6" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="6" rel="prev">
                <span class="ux-search-sr-only">Previous page</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"></path></svg>
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=1" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="1">
                1
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <span class="ux-search-pagination__link ux-search-pagination__ellipsis">...</span>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=5" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="5">
                5
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=6" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="6">
                6
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <span class="ux-search-pagination__link is-current">7</span>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=8" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="8">
                8
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=9" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="9">
                9
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <span class="ux-search-pagination__link ux-search-pagination__ellipsis">...</span>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=3334" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="3334">
                3334
            </a>
        </li>

        <li class="ux-search-pagination__item">
            <a class="ux-search-pagination__link" href="?page=8" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="8" rel="next">
                <span class="ux-search-sr-only">Next page</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg>
            </a>
        </li>
    </ul>
</nav>
```

## Styling

Default classes:
- `.ux-search-pagination` - Main navigation container
- `.ux-search-pagination__list` - List wrapper
- `.ux-search-pagination__item` - Individual list item
- `.ux-search-pagination__link` - Link/button element
- `.ux-search-pagination__link.is-current` - Current page indicator
- `.ux-search-pagination__ellipsis` - Ellipsis indicator

## Related Components

- [HitsPerPage](HitsPerPage.md) - Control results per page
- [TotalHits](TotalHits.md) - Show total results count
- [Hits](Hits.md) - Display search results
- [Layout](Layout.md) - Root container
