---
title: "Sub-pages with breadcrumb"
permalink: /jsonapi/subpages-breadcrumb/
excerpt: "How to retrieve the sub-pages with content and ancestors from Laravel CMS using the JSON:API to build page with breadcrumb"
---

To retrieve a specific page whose URL you've gotten from one of the previous responses (in the `links/self` attribute of the item) with its ancestor pages for the breadcrumb:

```
http://mydomain.tld/cms/pages/2?include=ancestors
```

Then, the `included` section contains the ancestor pages, e.g.:

```json
{
    "meta": {
        "baseurl": "http:\/\/mydomain.tld\/storage\/"
    },
    "jsonapi": {
        "version": "1.0"
    },
    "links": {
        "self": "http:\/\/mydomain.tld\/cms\/pages\/2"
    },
    "data": {
        "type": "pages",
        "id": "2",
        "attributes": {
            "parent_id": 1,
            "lang": "en",
            "path": "blog",
            "name": "Blog",
            "title": "Blog | Laravel CMS",
            "tag": "blog",
            "to": "",
            "domain": "",
            "has": true,
            "cache": 5,
            "content": [
                {
                    "text": "Blog example",
                    "type": "heading"
                },
                {
                    "type": "blog"
                }
            ],
            "meta": null,
            "config": null,
            "createdAt": "2023-05-01T09:36:30.000000Z",
            "updatedAt": "2023-05-01T09:36:30.000000Z"
        },
        "relationships": {
            "ancestors": {
                "data": [
                    {
                        "type": "navs",
                        "id": "1"
                    }
                ]
            }
        },
        "links": {
            "self": "http:\/\/mydomain.tld\/cms\/pages\/2"
        }
    },
    "included": [
        {
            "type": "navs",
            "id": "1",
            "attributes": {
                "parent_id": null,
                "lang": "en",
                "path": "/",
                "name": "Home",
                "title": "Home | Laravel CMS",
                "to": "",
                "domain": "mydomain.tld",
                "has": true,
                "cache": 5,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/1"
            }
        }
    ]
}
```
