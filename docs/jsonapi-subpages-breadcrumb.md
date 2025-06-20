---
title: "Sub-pages with breadcrumb"
permalink: /jsonapi/subpages-breadcrumb/
excerpt: "How to retrieve the sub-pages with content and ancestors from Laravel CMS using the JSON:API to build page with breadcrumb"
---

To retrieve a specific page whose URL you've gotten from one of the previous responses (in the `links/self` attribute of the item) with its ancestor pages for the breadcrumb and its shared content elements and files (add `?include=ancestors,elements,files` to the page URL):

```
http://mydomain.tld/api/cms/pages/2?include=ancestors,elements,files
```

The `included` section contains the list of shared element elements and files that should be displayed at the page if `include=elements,files` is added as parameter to the JSON:API URL the anchestor pages if the `include` parameter also contains `ancestors`, e.g.:

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
            "data": [
                {
                    "text": "Blog example",
                    "type": "cms::heading"
                },
                {
                    "type": "cms::blog"
                }
            ],
            "meta": null,
            "config": null,
            "createdAt": "2023-05-01T09:36:30.000000Z",
            "updatedAt": "2023-05-01T09:36:30.000000Z"
        },
        "relationships": {
            "elements": {
                "data": []
            },
            "ancestors": {
                "data": [
                    {
                        "type": "pages",
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
            "type": "pages",
            "id": "1",
            "attributes": {
                "parent_id": null,
                "lang": "en",
                "path": "/",
                "name": "Home",
                "title": "Home | Laravel CMS",
                "tag": "root",
                "to": "",
                "domain": "mydomain.tld",
                "has": true,
                "cache": 5,
                "data": null,
                "meta": null,
                "config": null,
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
