---
title: "Root page with navigation"
permalink: /jsonapi/root-navigation/
excerpt: "How to retrieve the root page and children from LaravelCMS using the JSON:API to build a page with navigation"
---

Retrieve the root page with content and first level sub-pages to build the navigation:

```
http://localhost/api/cms/pages?filter[tag]=root&include=children,contents
```

In case the site uses more than one language and sets the `lang` property for each page:

```
http://localhost/api/cms/pages?filter[tag]=root&filter[lang]=en&include=children,contents
```

The result will be a JSON:API response which looks like:

```json
{
    "meta": {
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
        "version": "1.0",
        "meta": {
            "baseurl": "\/storage\/"
        }
    },
    "links": {
        "first": "http:\/\/localhost:8000\/api\/cms\/pages?filter%5Btag%5D=root&include=children%2Ccontents&page%5Bnumber%5D=1&page%5Bsize%5D=15",
        "last": "http:\/\/localhost:8000\/api\/cms\/pages?filter%5Btag%5D=root&include=children%2Ccontents&page%5Bnumber%5D=1&page%5Bsize%5D=15"
    },
    "data": [
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
                "children": {
                    "data": [
                        {
                            "type": "pages",
                            "id": "2"
                        },
                        {
                            "type": "pages",
                            "id": "4"
                        },
                        {
                            "type": "pages",
                            "id": "5"
                        }
                    ]
                },
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/1\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/1\/relationships\/contents"
                    },
                    "data": [
                        {
                            "type": "contents",
                            "id": "0186d4a6-c532-70f7-939d-1f8d774aa72c"
                        }
                    ]
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/1"
            }
        }
    ],
    "included": [
        {
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
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/2\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/2\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/2"
            }
        },
        {
            "type": "pages",
            "id": "4",
            "attributes": {
                "parent_id": 1,
                "lang": "",
                "slug": "dev",
                "name": "Dev",
                "title": "For Developer | LaravelCMS",
                "tag": "",
                "to": "",
                "domain": "",
                "cache": 5,
                "data": null,
                "createdAt": "2023-03-12T07:09:05.000000Z",
                "updatedAt": "2023-03-12T07:09:05.000000Z"
            },
            "relationships": {
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/4\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/4\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/4"
            }
        },
        {
            "type": "pages",
            "id": "5",
            "attributes": {
                "parent_id": 1,
                "lang": "",
                "slug": "hidden",
                "name": "Hidden",
                "title": "Hidden page | LaravelCMS",
                "tag": "hidden",
                "to": "",
                "domain": "",
                "cache": 5,
                "data": null,
                "createdAt": "2023-03-12T07:09:05.000000Z",
                "updatedAt": "2023-03-12T07:09:05.000000Z"
            },
            "relationships": {
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/5\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/5\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/5"
            }
        },
        {
            "type": "contents",
            "id": "0186d4a6-c532-70f7-939d-1f8d774aa72c",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Welcome to Laravel CMS",
                    "type": "cms::heading"
                },
                "createdAt": "2023-03-12T07:09:04.000000Z"
            }
        }
    ]
}
```
