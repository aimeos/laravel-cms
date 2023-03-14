---
title: "Sub-pages with breadcrumb"
permalink: /jsonapi/subpages-breadcrumb/
excerpt: "How to retrieve the sub-pages with content and ancestors from Laravel CMS using the JSON:API to build page with breadcrumb"
---

To retrieve a specific page whose URL you've gotten from one of the previous responses (in the `links/self` attribute of the item) with its ancestor pages for the breadcrumb and its content (add `?include=ancestors,contents` to the page URL):

```
http://mydomain.tld/api/cms/pages/2?include=ancestors,contents
```

The `included` section contains the list of content elements that should be displayed at the page if `include=contents` is added as parameter to the JSON:API URL the anchestor pages if the `include` parameter also contains `ancestors`, e.g.:

```json
{
    "meta": {
        "baseurl": "http:\/\/mydomain.tld\/storage\/"
    },
    "jsonapi": {
        "version": "1.0"
    },
    "links": {
        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2"
    },
    "data": {
        "type": "pages",
        "id": "2",
        "attributes": {
            "parent_id": 1,
            "lang": "",
            "slug": "blog",
            "name": "Blog",
            "title": "Blog | Laravel CMS",
            "tag": "blog",
            "to": "",
            "domain": "",
            "cache": 5,
            "data": null,
            "createdAt": "2023-03-12T16:06:26.000000Z",
            "updatedAt": "2023-03-12T16:06:26.000000Z"
        },
        "relationships": {
            "contents": {
                "links": {
                    "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/contents",
                    "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/relationships\/contents"
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
            "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2"
        }
    },
    "included": [
        {
            "type": "contents",
            "id": "0186d692-be63-7611-a960-926d29954be3",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Blog example",
                    "type": "cms::heading"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-be82-7bf7-96fe-f35f8d0b77b8",
            "attributes": {
                "lang": "",
                "data": {
                    "type": "cms::blog"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
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
                "contents": {
                    "links": {
                        "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/contents",
                        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
            }
        }
    ]
}
```
