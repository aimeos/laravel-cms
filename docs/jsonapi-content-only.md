---
title: "Content only"
permalink: /jsonapi/content-only/
excerpt: "How to retrieve the content for already fetched pages from LaravelCMS using the JSON:API"
---

If you already got the page from a previous request (e.g. by adding `subtree` to the `include` parameter), then you only need the page content to render the complete page for the user. The page items in the `included` sections contains a URL to retrieve only that data in `relationships/contents/links/related`, e.g.:

```
http://mydomain.tld/api/cms/pages/3/contents
```

With that URL, you can fetch the page content now:

```json
{
    "meta": {
        "baseurl": "http:\/\/mydomain.tld\/storage\/",
        "page": {
            "currentPage": 1,
            "from": 1,
            "lastPage": 1,
            "perPage": 50,
            "to": 6,
            "total": 6
        }
    },
    "jsonapi": {
        "version": "1.0"
    },
    "links": {
        "first": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/contents?page%5Bnumber%5D=1&page%5Bsize%5D=50",
        "last": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/contents?page%5Bnumber%5D=1&page%5Bsize%5D=50",
        "related": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/contents",
        "self": "http:\/\/mydomain.tld\/api\/cms\/pages\/3\/relationships\/contents"
    },
    "data": [
        {
            "type": "contents",
            "id": "0186d692-beca-7366-9de5-8ea8ffb6d999",
            "attributes": {
                "lang": "",
                "data": {
                    "type": "cms::article",
                    "cover": {
                        "name": "Welcome to LaravelCMS",
                        "path": "https:\/\/aimeos.org\/tips\/wp-content\/uploads\/2023\/01\/ai-ecommerce-2.jpg",
                        "type": "cms::image",
                        "previews": {
                            "1000": "https:\/\/aimeos.org\/tips\/wp-content\/uploads\/2023\/01\/ai-ecommerce-2.jpg"
                        }
                    },
                    "intro": "LaravelCMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to millions of pages",
                    "title": "Welcome to LaravelCMS"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-beff-7e26-8a33-3987d66af4cc",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Rethink content management!",
                    "type": "cms::heading",
                    "level": 2
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-bf11-79ba-94af-acd0209c1671",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "LaravelCMS is exceptional in every way. Headless and API-first!",
                    "type": "cms::text"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-bf22-70ff-8760-2534a3681c2d",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "API first!",
                    "type": "cms::heading",
                    "level": 2
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-bf35-7061-8211-1b29102fef2c",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Use GraphQL for editing the pages, contents and files:",
                    "type": "cms::text"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "contents",
            "id": "0186d692-bf48-790d-90bd-e1ce3c17068c",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "mutation {\n  cmsLogin(email: \"editor@example.org\", password: \"secret\") {\n    name\n    email\n  }\n}",
                    "type": "cms::code",
                    "language": "graphql"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        }
    ]
}
```