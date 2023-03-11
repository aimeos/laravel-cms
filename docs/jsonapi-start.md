---
title: "Quick-Start Guide"
permalink: /jsonapi/start/
excerpt: "How to retrieve the data most often used from LaravelCMS"
classes: wide
---

## Root page

Retrieve the root page with content and first level sub-pages to build the navigation:

```
http://localhost/api/cms/pages?filter[tag]=root&include=children,content
// with locale
http://localhost/api/cms/pages?filter[tag]=root&filter[lang]=en&include=children,content
```

## Root page with mega-menu

Retrieve the root page with content and up to three levels of sub-pages to build a mega-menu:

```
http://localhost/api/cms/pages?filter[tag]=root&include=subtree,content
// with locale
http://localhost/api/cms/pages?filter[tag]=root&filter[lang]=en&include=subtree,content
```

## Subsequent pages with breadcrumb

Retrieve a specific page whose URL you've gotten from one of the previous responses with its ancestor pages for the breadcrumb and its content (add `?include=ancestors,content` to the page URL):

```
http://localhost/api/cms/pages/<ID>?include=ancestors,content
```

The `included` section contains the list of content elements that should be displayed at the page if `include=content` is added as parameter to the JSON:API URL. The `attributes` section of each content element contains a `type` key and arbitray keys/value pairs that depend on the content type, e.g.:

```json
"included": [{
    "type": "contents",
    "id": "0186a28d-3a20-7e98-a2a2-db7da878e79e",
    "attributes": {
        "data": [
            "Blog example",
            "cms::heading"
        ],
        "createdAt": "2023-03-02T13:40:09.000000Z"
    },
    "links": {
        "self": "http:\/\/localhost:8000\/api\/cms\/contents\/0186a28d-3a20-7e98-a2a2-db7da878e79e"
    }
},
{
    "type": "contents",
    "id": "0186a28d-3a2b-7adb-a16b-187bbec1b984",
    "attributes": {
        "data": [
            "cms::blog"
        ],
        "createdAt": "2023-03-02T13:40:09.000000Z"
    },
    "links": {
        "self": "http:\/\/localhost:8000\/api\/cms\/contents\/0186a28d-3a2b-7adb-a16b-187bbec1b984"
    }
}]
```
