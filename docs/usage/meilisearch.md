# Meilisearch

## Available configuration for adapter 

| Constant name                 | Meilisearch name      | Type     | Default value |
|-------------------------------|-----------------------|----------|---------------|
| ATTRIBUTES_TO_RETRIEVE_PARAM  | attributesToRetrieve  | string[] | ['*']         |
| ATTRIBUTES_TO_CROP_PARAM      | attributesToCrop      | string[] | []            |
| CROP_LENGTH_PARAM             | cropLength            | int      | 10            |
| CROP_MARKER_PARAM             | cropMarker            | string   | ...           |
| ATTRIBUTES_TO_HIGHLIGHT_PARAM | attributesToHighlight | string[] | []            |
| HIGHLIGHT_PRE_TAG_PARAM       | highlightPreTag       | string   | <em>          |
| HIGHLIGHT_POST_TAG_PARAM      | highlightPostTag      | string   | </em>         |

If you need more inforamtion about this configuration check [Meilisearch documentation](https://www.meilisearch.com/docs/reference/api/search#body)
