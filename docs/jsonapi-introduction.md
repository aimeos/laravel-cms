---
title: "Introduction"
permalink: /jsonapi/introduction/
excerpt: "How to fetch data from Laravel CMS using the JSON:API"
---

The Laravel CMS JSON frontend API follows the JSON:API standard documented at [jsonapi.org](https://jsonapi.org) and is available at (replace "mydomain.tld" with your own one):

```
http://mydomain.tld/api/cms/pages
```

The `pages` endpoint will return items from the page tree as well as related content elements depending on parameters added.

* [Available properties](#available-properties)
  * [Page properties](#page-properties)
  * [Content properties](#content-properties)
* [URL parameters](#url-parameters)
  * [Filter results](#filter-results)
  * [Include resources](#include-resources)
  * [Pagination](#pagination)
  * [Sparse fields](#sparse-fields)
* [Responses](#responses)
  * [Meta](#meta)
    * [Base URL](#base-url)
    * [Paged results](#paged-results)
  * [Links](#links)
  * [Data](#data)
    * [Single item](#single-item)
    * [Multiple item](#multiple-items)
    * [Relationships](#relationships)
  * [Included](#included)

## Available properties

### Page properties

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
        "title": "Home | Laravel CMS",
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

### Content properties

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

## URL parameters

### Filter results

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

### Include resources

When including related resources, you can get all data you need to render the page including the navigation in one request. The available related resources are:

content
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
http://mydomain.tld/api/cms/pages?filter[tag]=blog&include=ancestors,content
```

There are detailed examples for the most often used requests available:

- [Root page with navigation](jsonapi-root-navigation.md)
- [Root page with mega-menu](jsonapi-root-megamenu.md)
- [Sub-pages with breadcrumb](jsonapi-subpages-breadcrumb.md)
- [Page content only](jsonapi-content-only.md)

### Pagination

You can paginate through the results by adding the `page` parameter to the `/api/cms/pages` URL. The supported values are:

number
: Number of the slice that should be fetched starting from "1" up to the total number of available pages (see the [pagination respone for details](#paged-results))

size
: Number of items that should be fetched with a minimum value of "1" and a maximum value of "100". The default values are "15" for pages and "50" for contents

To get item 25 to 50 from the `pages` endpoint use:

```
http://mydomain.tld/api/cms/pages?page[number]=2&page[size]=25
```

This can be combined with `filter` and `include` parameters too:

```
http://mydomain.tld/api/cms/pages?filter[lang]=en&include=content&page[number]=2&page[size]=25
```

It does also work with related content links to load more content elements if the user scrolls down the page:

```
http://mydomain.tld/api/cms/pages/3/content?page[number]=2&page[size]=5
```

In the last case, use the [link](#links) instead of constructing the URL yourself!

### Sparse fields

Most often, you don't need all page or content properties and you can reduce the amount of data returned in the response by using the `fields` parameter. The requested fields can be limited for pages and content elements separately and the property names must be concatenated by comma.

To retrieve the `slug` and `lang` of the root pages only and the `data` property of the content elements, use:

```
http://mydomain.tld/api/cms/pages?include=content&fields[pages]=slug,lang&fields[contents]=data
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
            "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
        }
    }
]
```

The `type` and `id` of each item is always returned outside the `attributes` and can't be skipped!

## Responses

In the JSON-encoded response, there are three sections which are important:

- [meta](#meta)
- [links](#links)
- [data](#data)
- [included](#included)

### Meta

#### Base URL

The meta section always contains the `baseurl` key which is the base URL to all files/images referenced in the page head data or the content elements. Typically, you will see this in most responses:

```json
"meta": {
    "baseurl": "http:\/\/mydomain.tld\/storage\/"
}
```

In Laravel, you can change the base URL in the `./config/filesystems.php` file where you need to change the `url` setting for the `disk` Laravel CMS is using (`public` by default).

#### Paged results

Responses which returns a collection of pages (`/api/cms/pages`) and contents (e.g. `/api/cms/pages/1/content`), you will also notice a `page` key in the `meta` section which contains the pagination information:

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
: Maximum number of items returned in one response which is the passed `page[size]` value. The minimum value is "1", the maximum value is "100" and the default values are "15" for pages and "50" for contents

total
: Total number of available pages when using the same `size` value

## Links

The `links` section in the JSON API response is always included and contains the `self` link which would return the same response again:

```json
"links": {
    "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
},
```

Every time a collection of items is returned (e.g. by the `pages` or `pages/<id>/content` endpoints), there will be links for the first, last, previous and next results, e.g.:

```json
"links": {
    "first": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/content?page[number]=1&page[size]=2",
    "last": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/content?page[number]=3&page[size]=2",
    "next": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/content?page[number]=3&page[size]=2",
    "prev": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/content?page[number]=1&page[size]=2",
},
```

Thus, you can always use the links to fetch data and don't have to construct the links yourself!

## Data

The `data` section of the JSON:API response contains either a single resource (in case of e.g. `/api/cms/pages/1`) or a collection of resources (for `/api/cms/pages` or `/api/cms/pages/1/content`).

### Single item

Using a request which returns a single page, then the response is like:

```json
"data": {
    "type": "pages",
    "id": "1",
    "attributes": {
        "parent_id": null,
        "lang": "",
        "slug": "",
        "name": "Home",
        "title": "Home | Laravel CMS",
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
    },
    "relationships": {
        "content": {
            "links": {
                "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/content",
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/relationships\/content"
            }
        }
    },
    "links": {
        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
    }
}
```

The `data` section contains exactly one object with `type` and `id` properties which uniquely identifies the resource. Within the `attributes` part, the page properties are listed like shown above but could be also less if you've requested only [specific fields](#sparse-fields). In the `links` part, the `self` URL to retrieve the same page data is included. The `relationships` part is described in the [relationships section](#relationships) of this document.

### Multiple items

For request returning multiple items, the `data` section will be similar to:

```json
"data": [
    {
        "type": "pages",
        "id": "1",
        "attributes": {
            "parent_id": null,
            "lang": "",
            "slug": "",
            "name": "Home",
            "title": "Home | Laravel CMS",
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
        },
        "relationships": {
            "content": {
                "links": {
                    "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/content",
                    "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/relationships\/content"
                }
            }
        },
        "links": {
            "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
        }
    }
]
```

It's the same like for responses returning single resources but the `data` section contains a list of page items. If you call the URL inside `relationships/content/links/related`, you will get a list of content items:

```json
"data": [
    {
        "type": "contents",
        "id": "0186d692-be0b-798c-9450-0a676209b7a6",
        "attributes": {
            "lang": "",
            "data": {
                "text": "Welcome to Laravel CMS",
                "type": "cms::heading"
            },
            "created_at": "2023-03-12T16:06:26.000000Z"
        }
    }
]
```

### Relationships

By default, the `relationships` section contains a link to retrieve the content for that page (`/api/cms/pages/1/content`). If you use the [include parameter](#include-resources) to get related resources in the same request there will be a key for each related resource below `relationships`.

For a request which should include the parent page, ancestor pages, child pages, the page subtree and the content like:

```
http://mydomain.tld/api/cms/pages/1?include=parent,ancestors,children,subtree,content
```

Then, the `relationships` section will contain:

```json
"relationships": {
    "content": {
        "links": {
            "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/content",
            "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/relationships\/content"
        },
        "data": [
            {
                "type": "contents",
                "id": "0186d692-be63-7611-a960-926d29954be3"
            },
            {
                "type": "contents",
                "id": "0186d692-be82-7bf7-96fe-f35f8d0b77b8"
            }
        ]
    },
    "parent": {
        "data": {
            "type": "pages",
            "id": "1"
        }
    },
    "children": {
        "data": [
            {
                "type": "pages",
                "id": "3"
            }
        ]
    },
    "ancestors": {
        "data": [
            {
                "type": "pages",
                "id": "1"
            }
        ]
    },
    "subtree": {
        "data": [
            {
                "type": "pages",
                "id": "3"
            }
        ]
    }
},
```

Each key in the `relationships` part will a reference to a single item (like for `parent`) or references to multiple items in their `data` sections. The items itself will be part of the [`included` section](#included) of the returned response. In case of the root page, the `parent/data` key can also be NULL because there's no parent page for the root page any more:

```json
"relationships": {
    "parent": {
        "data": null
    }
},
```

## Included

The `included` section of each JSON API response is only available if you've added the `include` parameter to the URL, e.g. `/api/cms/pages/1?include=content`. In that case the `relationships/content/data` part contains the list of references:

```json
{
    "type": "pages",
    "id": "1",
    "attributes": {
        "name": "Home",
        "title": "Home | Laravel CMS",
        "tag": "root",
        "more keys": "..."
    },
    "relationships": {
        "content": {
            "data": [
                {
                    "type": "contents",
                    "id": "0186d692-be0b-798c-9450-0a676209b7a6"
                }
            ]
        }
    }
}
```

And the `included` section for that response then contains:

```json
"included": [
    {
        "type": "contents",
        "id": "0186d692-be0b-798c-9450-0a676209b7a6",
        "attributes": {
            "lang": "",
            "data": {
                "text": "Welcome to Laravel CMS",
                "type": "cms::heading"
            },
            "created_at": "2023-03-12T16:06:26.000000Z"
        }
    }
]
```

It consists of a flat list of page or content items identified by their `type` and `id` values. You must now match the type and ID within the `relationships/content` section with the type and ID within the `included` section.