---
title: "Sub-pages with breadcrumb"
permalink: /jsonapi/subpages-breadcrumb/
excerpt: "How to retrieve the sub-pages with content and ancestors from LaravelCMS using the JSON:API to build page with breadcrumb"
---

To retrieve a specific page whose URL you've gotten from one of the previous responses (in the `links/self` attribute of the item) with its ancestor pages for the breadcrumb and its content (add `?include=ancestors,contents` to the page URL):

```
http://localhost/api/cms/pages/<ID>?include=ancestors,contents
```

The `included` section contains the list of content elements that should be displayed at the page if `include=contents` is added as parameter to the JSON:API URL the anchestor pages if the `include` parameter also contains `ancestors`, e.g.:

```json
{
    "jsonapi": {
        "version": "1.0",
        "meta": {
            "baseurl": "\/storage\/"
        }
    },
    "links": {
        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/2"
    },
    "data": {
        "type": "pages",
        "id": "2",
        "attributes": {
            "parent_id": 1,
            "lang": "",
            "slug": "blog",
            "name": "Blog",
            "title": "Blog | LaravelCMS",
            "tag": "blog",
            "to": "",
            "domain": "",
            "cache": 5,
            "data": null,
            "createdAt": "2023-03-12T07:09:04.000000Z",
            "updatedAt": "2023-03-12T07:09:04.000000Z"
        },
        "relationships": {
            "ancestors": {
                "data": [
                    {
                        "type": "pages",
                        "id": "1"
                    }
                ]
            },
            "contents": {
                "links": {
                    "related": "http:\/\/localhost:8000\/api\/cms\/pages\/2\/contents",
                    "self": "http:\/\/localhost:8000\/api\/cms\/pages\/2\/relationships\/contents"
                },
                "data": [
                    {
                        "type": "contents",
                        "id": "0186d4a6-c5ed-7437-a1c0-5932323f4f64"
                    },
                    {
                        "type": "contents",
                        "id": "0186d4a6-c610-7497-b94b-8eda51ae0b1c"
                    }
                ]
            }
        },
        "links": {
            "self": "http:\/\/localhost:8000\/api\/cms\/pages\/2"
        }
    },
    "included": [
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
                "createdAt": "2023-03-12T07:09:04.000000Z",
                "updatedAt": "2023-03-12T07:09:04.000000Z"
            },
            "relationships": {
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/1\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/1\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/1"
            }
        },
        {
            "type": "contents",
            "id": "0186d4a6-c5ed-7437-a1c0-5932323f4f64",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Blog example",
                    "type": "cms::heading"
                },
                "createdAt": "2023-03-12T07:09:04.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d4a6-c610-7497-b94b-8eda51ae0b1c",
            "attributes": {
                "lang": "",
                "data": {
                    "type": "cms::blog"
                },
                "createdAt": "2023-03-12T07:09:04.000000Z"
            }
        }
    ]
}
```
