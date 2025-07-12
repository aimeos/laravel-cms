---
title: "Root page with mega-menu"
permalink: /jsonapi/root-megamenu/
excerpt: "How to retrieve the root page with descendents from Laravel CMS using the JSON:API to build a page with mega-menu"
---

## Root page with mega-menu

Retrieve the root page with sub-pages up to three levels to build a mega-menu:

```
http://mydomain.tld/cms/pages?filter[path]=/&include=subtree
```

Then, the page tree (up to three levels deep) for the root page will be returned:

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
        "first": "http:\/\/mydomain.tld\/cms\/pages?filter%5Bpath%5D=/&include=subtree&page%5Bnumber%5D=1&page%5Bsize%5D=15",
        "last": "http:\/\/mydomain.tld\/cms\/pages?filter%5Bpath%5D=/&include=subtree&page%5Bnumber%5D=1&page%5Bsize%5D=15"
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
                "subtree": {
                    "data": [
                        {
                            "type": "navs",
                            "id": "2"
                        },
                        {
                            "type": "navs",
                            "id": "3"
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
            "id": "3",
            "attributes": {
                "parent_id": 2,
                "lang": "en",
                "path": "welcome-to-laravelcms",
                "name": "Welcome to Laravel CMS",
                "title": "Welcome to Laravel CMS | Laravel CMS",
                "to": "",
                "domain": "",
                "has": false,
                "cache": 5,
                "createdAt": "2023-05-01T09:36:30.000000Z",
                "updatedAt": "2023-05-01T09:36:30.000000Z"
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/cms\/pages\/3"
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
