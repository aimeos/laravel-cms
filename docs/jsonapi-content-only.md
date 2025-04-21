---
title: "Shared elements only"
permalink: /jsonapi/element-only/
excerpt: "How to retrieve the element for already fetched pages from Laravel CMS using the JSON:API"
---

If you already got the page from a previous request (e.g. by adding `subtree` to the `include` parameter), then you only need the page element to render the complete page for the user. The page items in the `included` sections contains a URL to retrieve only that data in `relationships/element/links/related`, e.g.:

```
http://mydomain.tld/api/cms/pages/3/element
```

With that URL, you can fetch the page element now:

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
        "first": "http:\/\/mydomain.tld\/cms\/pages\/3\/element?page%5Bnumber%5D=1&page%5Bsize%5D=50",
        "last": "http:\/\/mydomain.tld\/cms\/pages\/3\/element?page%5Bnumber%5D=1&page%5Bsize%5D=50",
        "related": "http:\/\/mydomain.tld\/cms\/pages\/3\/element",
        "self": "http:\/\/mydomain.tld\/cms\/pages\/3\/relationships\/element"
    },
    "data": [
        {
            "type": "elements",
            "id": "0186d692-beca-7366-9de5-8ea8ffb6d999",
            "attributes": {
                "lang": "",
                "data": {
                    "type": "cms::article",
                    "cover": {
                        "name": "Welcome to Laravel CMS",
                        "path": "https:\/\/aimeos.org\/tips\/wp-element\/uploads\/2023\/01\/ai-ecommerce-2.jpg",
                        "type": "cms::image",
                        "previews": {
                            "1000": "https:\/\/aimeos.org\/tips\/wp-element\/uploads\/2023\/01\/ai-ecommerce-2.jpg"
                        }
                    },
                    "intro": "Laravel CMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to millions of pages",
                    "title": "Welcome to Laravel CMS"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "elements",
            "id": "0186d692-beff-7e26-8a33-3987d66af4cc",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Rethink element management!",
                    "type": "cms::heading",
                    "level": 2
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "elements",
            "id": "0186d692-bf11-79ba-94af-acd0209c1671",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Laravel CMS is exceptional in every way. Headless and API-first!",
                    "type": "cms::text"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "elements",
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
            "type": "elements",
            "id": "0186d692-bf35-7061-8211-1b29102fef2c",
            "attributes": {
                "lang": "",
                "data": {
                    "text": "Use GraphQL for editing the pages, elements and files:",
                    "type": "cms::text"
                },
                "createdAt": "2023-03-12T16:06:26.000000Z"
            }
        },
        {
            "type": "elements",
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

If the page has more than 50 element elements, use the `next` link in the `links` section to fetch the next 50 element elements!