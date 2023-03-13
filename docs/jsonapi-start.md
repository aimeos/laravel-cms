---
title: "Quick-Start Guide"
permalink: /jsonapi/start/
excerpt: "How to fetch data from LaravelCMS using the JSON:API"
---

The JSON frontend API follows the JSON:API standard documented at [jsonapi.org](https://jsonapi.org). In the LaravelCMS response, there are three sections which are important:

- meta
- data
- included

## Meta

### Base URL

The meta section always contains the `baseurl` key which is the base URL to all files/images referenced in the page head data or the content elements. Typically, you will see this in most responses:

```json
"meta": {
    "baseurl": "http:\/\/mydomain.tld\/storage\/"
}
```

In Laravel, you can change the base URL in the `./config/filesystems.php` file where you need to change the `url` setting for the `disk` LaravelCMS is using (`public` by default).

### Paging results

Responses which returns a collection of pages (`/api/cms/pages`) and contents (e.g. `/api/cms/pages/1/contents`), you will also notice a `page` key in the `meta` section which contains the pagination information:

```json
"meta": {
    "page": {
        "currentPage": 1,
        "from": 1,
        "lastPage": 1,
        "perPage": 15,
        "to": 1,
        "total": 1
    }
}
```

Important key/value pairs are:

currentPage
: Page number of the current page (starts with "1")

lastPage
: Page number of the last page available when using the same `perPage` value. Minimum value is "1", the maximum value is "100"

perPage
: Maximum number of items returned in one response. The minimum value is "1", the maximum value is "100" and the default values are "15" for pages and "50" for contents

total
: Total number of available pages when using the same `perPage` value

You can page through the results by adding `page[number]` and `page[size]` to the used URLs, e.g.

```
http://mydomain.tld/api/cms/pages?page[number]=2&page[size]=25
```

This can be combined with `filter` and `include` parameters too:

```
http://mydomain.tld/api/cms/pages?filter[lang]=en&include=contents&page[number]=2&page[size]=25
```

It does also work with related contents links to load more content elements if the user scrolls down the page:

```
http://mydomain.tld/api/cms/pages/3/contents?page[number]=2&page[size]=5
```
