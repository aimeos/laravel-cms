---
title: "Root page with navigation"
permalink: /jsonapi/root-navigation/
excerpt: "How to retrieve the root page and children from Laravel CMS using the JSON:API to build a page with navigation"
---

Retrieve the root page with content and first level sub-pages to build the navigation:

```
http://mydomain.tld/api/cms/pages?filter[tag]=root&include=children,content
```

In case the site uses more than one language and sets the `lang` property for each page:

```
http://mydomain.tld/api/cms/pages?filter[tag]=root&filter[lang]=en&include=children,content
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
        "first": "http:\/\/mydomain.tld\/api\/cms\/pages?filter%5Btag%5D=root&include=children%2Ccontent&page%5Bnumber%5D=1&page%5Bsize%5D=15",
        "last": "http:\/\/mydomain.tld\/api\/cms\/pages?filter%5Btag%5D=root&include=children%2Ccontent&page%5Bnumber%5D=1&page%5Bsize%5D=15"
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
                    },
                    "data": [
                        {
                            "type": "contents",
                            "id": "0186d692-be0b-798c-9450-0a676209b7a6"
                        }
                    ]
                },
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
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/1"
            }
        }
    ],
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
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
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
                "content": {
                    "links": {
                        "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/content",
                        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2\/relationships\/content"
                    }
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/2"
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
                "title": "For Developer | Laravel CMS",
                "tag": "",
                "to": "",
                "domain": "",
                "cache": 5,
                "data": null,
                "createdAt": "2023-03-12T16:06:26.000000Z",
                "updatedAt": "2023-03-12T16:06:26.000000Z"
            },
            "relationships": {
                "content": {
                    "links": {
                        "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/4\/content",
                        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/4\/relationships\/content"
                    }
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/4"
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
                "title": "Hidden page | Laravel CMS",
                "tag": "hidden",
                "to": "",
                "domain": "",
                "cache": 5,
                "data": null,
                "createdAt": "2023-03-12T16:06:27.000000Z",
                "updatedAt": "2023-03-12T16:06:27.000000Z"
            },
            "relationships": {
                "content": {
                    "links": {
                        "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/5\/content",
                        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/5\/relationships\/content"
                    }
                }
            },
            "links": {
                "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/5"
            }
        }
    ]
}
```
