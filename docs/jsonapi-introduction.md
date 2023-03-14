---
title: "Introduction"
permalink: /jsonapi/introduction/
excerpt: "How to fetch data from LaravelCMS using the JSON:API"
---

The LaravelCMS JSON frontend API follows the JSON:API standard documented at [jsonapi.org](https://jsonapi.org) and is available at (replace "mydomain.tld" with your own one):

```
http://mydomain.tld/api/cms/pages
```

The `pages` endpoint will return items from the page tree as well as related content elements depending on parameters added.

The available page properties are:

```json
{
    "type": "pages",
    "id": "1",
    "attributes": {
        "parent_id": null,
        "lang": "",
        "slug": "",
        "name": "Home",
        "title": "Home | LaravelCMS",
        "tag": "root",
        "to": "",
        "domain": "mydomain.tld",
        "cache": 5,
        "data": {
            "meta": {
                "text": "Laravel CMS is outstanding",
                "type": "cms::meta"
            }
        },
        "createdAt": "2023-03-12T16:06:26.000000Z",
        "updatedAt": "2023-03-12T16:06:26.000000Z"
    }
}
```

parent_id
: ID of the parent page

lang
: ISO language code, either two letters in lower case (e.g. "en") or five characters for country specific languages (e.g. "en_US")

slug
: URL segment of the page (must not contain any slashes)

name
: Short page name for navigation

title
: Page title like shown in the browser

tag
: Arbitrary name which can be used for filtering to get a specific page

to
: URL to the target page in case the page is redirecting to another page

domain
: Domain name the (root) page is responsible for

cache
: How long the returned response (and therefore the generated page can be cached

data
: Set of arbitrary page meta data that should be part of the page head

createdAt
: ISO date/time when the page was created

updatedAt
: ISO date/time when the page was last modified

The available content properties are:

```json
{
    "type": "contents",
    "id": "0186d692-be0b-798c-9450-0a676209b7a6",
    "attributes": {
        "lang": "",
        "data": {
            "text": "Welcome to Laravel CMS",
            "type": "cms::heading"
        },
        "createdAt": "2023-03-12T16:06:26.000000Z"
    }
}
```

lang
: ISO language code, either two letters in lower case (e.g. "en") or five characters for country specific languages (e.g. "en_US")

data
: Arbitrary content element that should be part of the page body

createdAt
: ISO date/time when the content element was created

## Filter results

To limit the returned items exactly to the ones you want, the JSON API supports one or more `filter` parameters:

domain
: Filters the pages by domain name which must be set at least in the root page in the `domain` property

tag
: Returns only pages (or one page) where the passed value is set in the `tag` property of the page item

lang
: In case of multi-language page trees, this parameter limits the pages to the specified language set in the `lang` property of the page item

To get the page tagged with `root` for the domain `mydomain.tld` in English, use:

```
http://mydomain.tld/api/cms/pages?filter[tag]=root&filter[domain]=mydomain.tld&filter[lang]=en
```

## Include resources

When including related resources, you can get all data you need to render the page including the navigation in one request. The available related resources are:

contents
: List of content elements for the requested page (paginated if more than 50 items)

parent
: Parent page item

ancestors
: All parent pages up to the root page

children
: List of direct child pages for the requested page (paginated if more than 15 items)

subtree
: Tree of sub-pages up to three levels deep for building a mega-menu

To get the page tagged with `blog` including its ancestors and content elements use:

```
http://mydomain.tld/api/cms/pages?filter[tag]=blog&include=ancestors,contents
```

There are detailed examples for the most often used requests available:

- [Root page with navigation](jsonapi-root-navigation.md)
- [Root page with mega-menu](jsonapi-root-megamenu.md)
- [Sub-pages with breadcrumb](jsonapi-subpages-breadcrumb.md)
- [Page content only](jsonapi-content-only.md)

## Pagination

You can paginate through the results by adding the `page` parameter to the `/api/cms/pages` URL. The supported values are:

number
: Number of the slice that should be fetched starting from "1" up to the total number of available pages (see the [pagination respone for details](#paged-results))

size
: Number of items that should be fetched with a minimum value of "1" and a maximum value of "100"

To get item 25 to 50 from the `pages` endpoint use:

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

## Limit fields

Most often, you don't need all page or content properties and you can reduce the amount of data returned in the response by using the `fields` parameter. The requested fields can be limited for pages and content elements separately and the property names must be concatenated by comma.

To retrieve the `slug` and `lang` of the root pages only and the `data` property of the content elements, use:

```
http://mydomain.tld/api/cms/pages?include=contents&fields[pages]=slug,lang&fields[contents]=data
```

Then, the attributes of the returned pages in the [data section](#data) will contain only:

```json
"data": [
    {
        "type": "pages",
        "id": "1",
        "attributes": {
            "lang": "",
            "slug": ""
        },
        "links": {
            "self": "http:\/\/localhost:8000\/api\/cms\/pages\/1"
        }
    }
]
```

The `type` and `id` of each item is always returned outside the `attributes` and can't be skipped!

## Response

In the JSON-encoded response, there are three sections which are important:

- meta
- data
- included

### Meta

#### Base URL

The meta section always contains the `baseurl` key which is the base URL to all files/images referenced in the page head data or the content elements. Typically, you will see this in most responses:

```json
"meta": {
    "baseurl": "http:\/\/mydomain.tld\/storage\/"
}
```

In Laravel, you can change the base URL in the `./config/filesystems.php` file where you need to change the `url` setting for the `disk` LaravelCMS is using (`public` by default).

#### Paged results

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
