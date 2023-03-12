---
title: "Quick-Start Guide"
permalink: /jsonapi/start/
excerpt: "How to retrieve the data from LaravelCMS using the JSON:API"
---

## Root page

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

## Root page with mega-menu

Retrieve the root page with content and up to three levels of sub-pages to build a mega-menu:

```
http://localhost/api/cms/pages?filter[tag]=root&include=subtree,contents
```

For multi-language sites, the `lang` filter parameter must be added:

```
http://localhost/api/cms/pages?filter[tag]=root&filter[lang]=en&include=subtree,contents
```

Then, the page tree (up to three levels deep) including the content for the root page will be returned:

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
        "first": "http:\/\/localhost:8000\/api\/cms\/pages?filter%5Btag%5D=root&include=subtree%2Ccontents&page%5Bnumber%5D=1&page%5Bsize%5D=15",
        "last": "http:\/\/localhost:8000\/api\/cms\/pages?filter%5Btag%5D=root&include=subtree%2Ccontents&page%5Bnumber%5D=1&page%5Bsize%5D=15"
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
                "subtree": {
                    "data": [
                        {
                            "type": "pages",
                            "id": "2"
                        },
                        {
                            "type": "pages",
                            "id": "3"
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
            "id": "3",
            "attributes": {
                "parent_id": 2,
                "lang": "",
                "slug": "welcome-to-laravelcms",
                "name": "Welcome to LaravelCMS",
                "title": "Welcome to LaravelCMS | LaravelCMS",
                "tag": "article",
                "to": "",
                "domain": "",
                "cache": 5,
                "data": null,
                "createdAt": "2023-03-12T07:09:04.000000Z",
                "updatedAt": "2023-03-12T07:09:05.000000Z"
            },
            "relationships": {
                "contents": {
                    "links": {
                        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/3\/contents",
                        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/3\/relationships\/contents"
                    }
                }
            },
            "links": {
                "self": "http:\/\/localhost:8000\/api\/cms\/pages\/3"
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

## Subsequent pages with breadcrumb

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

## Retrieve content only

If you already got the page from a previous request (e.g. by adding `subtree` to the `include` parameter), then you only need the content for that page to to show the complete page to the user. The page items in the `included` sections contains a URL to retrieve only that data in `relationships/contents/links/related`, e.g. `http:\/\/localhost:8000\/api\/cms\/pages\/3\/contents` with that URL, you can fetch the page content now:

```json
{
    "jsonapi": {
        "version": "1.0",
        "meta": {
            "baseurl": "\/storage\/"
        }
    },
    "links": {
        "related": "http:\/\/localhost:8000\/api\/cms\/pages\/3\/contents",
        "self": "http:\/\/localhost:8000\/api\/cms\/pages\/3\/relationships\/contents"
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