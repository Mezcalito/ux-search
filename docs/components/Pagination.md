# Pagination

The `Pagination` component displays a pagination system which lets users change the current page of search results.

## Block available

| name     | Description |
|----------|-------------|
| content  | -           |
| hit      | -           |
| noResult | -           |

## Default layout

```twig
{%- block content %}
    {%- if totalPage > 1 %}
        <nav {{ attributes.defaults({'class': 'ux-search-pagination'}) }} >
            <ul class="ux-search-pagination__list">
                {% if (page - 1) >= 1 %}
                    <li class="ux-search-pagination__item">
                        <a
                            class="ux-search-pagination__link"
                            href="?page={{ page - 1 }}"
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
    
                {% if startRange > range %}
                    {% for i in 1..range %}
                        <li class="ux-search-pagination__item">
                            {{ _self.link(i, page) }}
                        </li>
                    {% endfor %}
                {% endif %}
    
                {% if startRange - 1 == 1 %}
                    <li class="ux-search-pagination__item">
                        {{ _self.link(1, page) }}
                    </li>
                {% elseif startRange - 1 == range + 1 %}
                    <li class="ux-search-pagination__item">
                        {{ _self.link(range + 1, page) }}
                    </li>
                {% elseif startRange - 1 >= range + 2 %}
                    <li class="ux-search-pagination__item">
                        {{ _self.elipsis() }}
                    </li>
                {% endif %}
    
                {% for i in  startRange..endRange %}
                    <li class="ux-search-pagination__item">
                        {{ _self.link(i, page) }}
                    </li>
                {% endfor %}
    
                <li class="ux-search-pagination__item">
                    {% if endRange + range <= totalPage - range %}
                        {{ _self.elipsis() }}
                    {% elseif endRange + 1 == totalPage - (range + 1)  %}
                        {{ _self.link(endRange + 1, page) }}
                    {% endif %}
                </li>
    
                {% if endRange < totalPage %}
                    {% for i in (totalPage - range + 1)..totalPage %}
                        <li class="ux-search-pagination__item">
                            {{ _self.link(i, page) }}
                        </li>
                    {% endfor %}
                {% endif %}
    
                {% if page < totalPage %}
                    <li class="ux-search-pagination__item">
                        <a
                            class="ux-search-pagination__link{{ page < totalPage ? '' : ' is-disabled'}}"
                            href="?page={{ page + 1 }}"
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

    {%- macro link(iterator, page) %}
        {%- if page == iterator %}
            <span class="ux-search-pagination__link is-current">{{ iterator }}</span>
        {% else %}
            <a
                class="ux-search-pagination__link"
                href="?page={{ iterator }}"
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

## Default HTML output
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
            <a class="ux-search-pagination__link" href="?page=2" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="2">
                2
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
            <a class="ux-search-pagination__link" href="?page=3333" data-action="live#action:prevent" data-live-action-param="changeCurrentPage" data-live-page-param="3333">
                3333
            </a>
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
