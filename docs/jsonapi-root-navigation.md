---
title: "Root page with navigation"
permalink: /jsonapi/root-navigation/
excerpt: "How to retrieve the root page and children from Laravel CMS using the JSON:API to build a page with navigation"
---

Retrieve the root page and first level sub-pages to build the navigation:

```
http://mydomain.tld/cms/pages?filter[path]=/&include=children
```

The result will be a JSON:API response which looks like:

```json
{
    "meta": {
        "baseurl": "http:\/\/mydomain.tld\/storage\/",
        "page": {
            "currentPage": 1,
            "from": 1,
            "lastPage": 1,
            "perPage": 15,
            "to": 1,
            "total": 1
        }
    },
    "jsonapi": {
        "version": "1.0"
    },
    "links": {
        "first": "http:\/\/mydomain.tld\/cms\/pages?filter%5Bpath%5D=/&include=children&page%5Bnumber%5D=1&page%5Bsize%5D=15",
        "last": "http:\/\/mydomain.tld\/cms\/pages?filter%5Bpath%5D=/&include=children&page%5Bnumber%5D=1&page%5Bsize%5D=15"
    },
    "data": [
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
                "content": [
                    {
                        "text": "Welcome to Laravel CMS",
                        "type": "heading"
                    }
                ],
                "meta": {
                    "meta": {
                        "text": "Laravel CMS is outstanding",
                        "type": "meta"
                    }
                },
                "config": null,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "relationships": {
                "children": {
                    "data": [
                        {
                            "type": "navs",
                            "id": "2"
                        },
                        {
                            "type": "navs",
                            "id": "4"
                        },
                        {
                            "type": "navs",
                            "id": "5"
                        }
                    ]
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/1"
            }
        }
    ],
    "included": [
        {
            "type": "navs",
            "id": "2",
            "attributes": {
                "parent_id": 1,
                "lang": "en",
                "path": "blog",
                "name": "Blog",
                "title": "Blog | Laravel CMS",
                "to": "",
                "domain": "",
                "has": true,
                "cache": 5,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/2"
            }
        },
        {
            "type": "navs",
            "id": "4",
            "attributes": {
                "parent_id": 1,
                "lang": "en",
                "path": "dev",
                "name": "Dev",
                "title": "For Developer | Laravel CMS",
                "to": "",
                "domain": "",
                "has": false,
                "cache": 5,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/4"
            }
        },
        {
            "type": "navs",
            "id": "5",
            "attributes": {
                "parent_id": 1,
                "lang": "en",
                "path": "hidden",
                "name": "Hidden",
                "title": "Hidden page | Laravel CMS",
                "to": "",
                "domain": "",
                "has": false,
                "cache": 5,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/5"
            }
        }
    ]
}
```
