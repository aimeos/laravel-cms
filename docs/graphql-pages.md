---
title: "Pages"
permalink: /graphql/pages/
excerpt: "How to fetch, add, move, update, delete and restore pages in Laravel CMS using the GraphQL API"
---

* [Retrieve pages](#retrieve-pages)
  * [Fetch root pages](#fetch-root-pages)
  * [Fetch children](#fetch-children)
  * [Get page by ID](#get-page-by-id)
* [Add pages](#add-pages)
  * [Add new root page](#add-new-root-page)
  * [Add new child page](#add-new-child-page)
  * [Insert before page](#insert-before-page)
* [Move pages](#move-pages)
  * [Make page a root page](#make-page-a-root-page)
  * [Move page to new parent](#move-page-to-new-parent)
  * [Move page before](#move-page-before)
* [Save page properties](#save-page-properties)
* [Publish page meta data](#publish-page-metadata)
* [Delete and restore pages](#delete-and-restore-pages)
  * [Trash pages](#trash-pages)
  * [Restore pages](#restore-pages)
  * [Delete pages](#delete-pages)


## Retrieve pages

The pages are organized as tree with a root page and sub-pages. You can have several root pages for different domains and/or languages.

### Fetch root pages

To get the root pages use:

```graphql
query {
  pages(parent_id: null, lang: "", first: 10, page: 1) {
    data {
      id
      parent_id
      lang
      path
      domain
      name
      title
      to
      tag
      data
      config
      status
      cache
      start
      end
      has
      editor
      created_at
      updated_at
      deleted_at
    }
    paginatorInfo {
      currentPage
      lastPage
    }
  }
}
```

Required parameters are:

parent_id (optional)
: ID of the page e.g. retrieved by the root page (default: NULL)

lang (optional)
: ISO language code (e.g. "en", "en_US" or empty value) to retrieve the pages for (default: all)

first (optional)
: Maximum number of pages to retrieve (default: 50)

page (optional)
: Retrieve the pages starting from `first` multiplied by `page` offset (default: "1")

The result will be:

```json
{
  "data": {
    "pages": {
      "data": [
        {
          "id": "1",
          "parent_id": null,
          "lang": "",
          "path": "",
          "domain": "mydomain.tld",
          "name": "Home",
          "title": "Home | Laravel CMS",
          "to": "",
          "tag": "root",
          "data": "{\"meta\":{\"text\":\"Laravel CMS is outstanding\",\"type\":\"meta\"}}",
          "config": "{}",
          "status": 1,
          "cache": 5,
          "start": null,
          "end": null,
          "has": true,
          "editor": "aimeos@aimeos.org",
          "created_at": "2023-04-01 09:02:25",
          "updated_at": "2023-04-01 10:22:15",
          "deleted_at": null
        }
      ],
      "paginatorInfo": {
        "currentPage": 1,
        "lastPage": 1
      }
    }
  }
}
```

### Fetch children

To get the children of a page with a known ID use:

```graphql
query {
  pages(parent_id: 1, lang: "", first: 25, page: 1) {
    data {
      id
      lang
      path
      domain
      name
      title
      to
      tag
      data
      config
      status
      cache
      start
      end
      has
      editor
      created_at
      updated_at
      deleted_at
    }
    paginatorInfo {
      currentPage
      lastPage
    }
  }
}
```

Required parameters are:

parent_id
: ID of the page e.g. retrieved by the root page

lang (optional)
: ISO language code (e.g. "en", "en_US" or empty value) to retrieve the pages for (default: all)

first (optional)
: Maximum number of pages to retrieve (default: 50)

page (optional)
: Retrieve the pages starting from `first` multiplied by `page` offset (default: "1")

The query returns:

```json
{
  "data": {
    "pages": {
      "data": [
        {
          "id": "2",
          "lang": "",
          "path": "blog",
          "domain": "",
          "name": "Blog",
          "title": "Blog | Laravel CMS",
          "to": "",
          "tag": "blog",
          "data": "{}",
          "config": "{}",
          "status": 1,
          "cache": 5,
          "start": null,
          "end": null,
          "has": true,
          "editor": "aimeos@aimeos.org",
          "created_at": "2023-04-01 09:02:25",
          "updated_at": "2023-04-01 09:30:11",
          "deleted_at": null
        },
        {
          "id": "4",
          "lang": "",
          "path": "dev",
          "domain": "",
          "name": "Dev",
          "title": "For Developer | Laravel CMS",
          "to": "",
          "tag": "",
          "data": "{}",
          "config": "{}",
          "status": 1,
          "cache": 5,
          "start": null,
          "end": null,
          "has": false,
          "editor": "aimeos@aimeos.org",
          "created_at": "2023-04-01 09:02:25",
          "updated_at": "2023-04-01 09:30:11",
          "deleted_at": null
        },
      ],
      "paginatorInfo": {
        "currentPage": 1,
        "lastPage": 1
      }
    }
  }
}
```

## Get page by ID

To get a page itself by its ID:

```graphql
query {
  page(id: 1) {
    id
    lang
    path
    domain
    name
    title
    to
    tag
    data
    config
    status
    cache
    start
    end
    has
    editor
    created_at
    updated_at
    deleted_at
  }
}
```

Required parameters are:

id
: ID of the page

The returned JSON data will contain all page properties without an additional `data` and `paginatorInfo` key compared to the `pages()` query:

```json
{
  "data": {
    "page": {
      "id": "1",
      "lang": "",
      "path": "",
      "domain": "mydomain.tld",
      "name": "Home",
      "title": "Home | Laravel CMS",
      "to": "",
      "tag": "root",
      "meta": "{\"cms:meta\":{\"text\":\"Laravel CMS is outstanding\",\"type\":\"meta\"}}",
      "data": "[{\"type\":\"heading\",\"text\":\"Welcome to Laravel CMS\"}]",
      "config": "{}",
      "status": 1,
      "cache": 5,
      "start": null,
      "end": null,
      "has": true,
      "editor": "aimeos@aimeos.org",
      "created_at": "2023-04-01 09:02:25",
      "updated_at": "2023-04-01 10:22:15",
      "deleted_at": null
    }
  }
}
```

## Add pages

### Add new root page

To add a new root page at the end of the list of root pages use:

```graphql
mutation {
  addPage(input: {
    lang: "en",
    path: "test-url",
    domain: "mydomain.tld",
    name: "Test page",
    title: "A Laravel CMS test page",
    to: "https://laravel-cms.org",
    tag: "test",
    meta: "{}",
    data: "[]",
    config: "{}",
    status: 0,
    cache: 5,
    start: "2023-04-01 00:00:00",
    end: null,
    content: ["0187d6ab-b76d-75ee-8830-ab00b4259aa5", "0187d6ab-b76d-75ee-8840-7e8026251ba0"],
    files: ["0187d6ab-b76d-75ee-8b0d-1b59cc3a1ab7"]
  }) {
    id
  }
}
```

Required parameters are:

input
: JSON object with key/value pairs for the [page properties](../graphql-datatypes.md#page-properties)

All properties are optional. The input parameter contains two additional parameters which must be passed if shared content elements or files are referenced in the `data` or `meta` property:

content (list of IDs)
: List of IDs from the shared content elements which are referenced in `data` or `meta` sections

files (list of IDs)
: List of IDs from the files which are referenced in `data` or `meta` sections

The request will return:

```json
{
  "data": {
    "addPage": {
      "id": "9",
    }
  }
}
```

### Add new child page

To add a new child page to an existing parent page at the end of the list of children pages use:

```graphql
mutation {
  addPage(input: {
    lang: "en",
    path: "test-url-2",
    domain: "mydomain.tld",
    name: "Test page",
    title: "A Laravel CMS test page",
    to: "https://laravel-cms.org",
    tag: "test",
    meta: "{}",
    data: "[]",
    config: "{}",
    status: 0,
    cache: 5,
    start: "2023-04-01 00:00:00",
    end: null,
    content: ["0187d6ab-b76d-75ee-8830-ab00b4259aa5", "0187d6ab-b76d-75ee-8840-7e8026251ba0"],
    files: ["0187d6ab-b76d-75ee-8b0d-1b59cc3a1ab7"]
  }, parent_id: 1) {
    id
  }
}
```

Required parameters are:

input
: JSON object with key/value pairs for the [page properties](../graphql-datatypes.md#page-properties)

parent_id (optional)
: ID of the parent where the new page will be inserted below

The `content` and `files` properties in the `input` parameter must be passed if shared content elements or files are referenced in the `data` or `meta` property:

content (list of IDs)
: List of IDs from the shared content elements which are referenced in `data` or `meta` sections

files (list of IDs)
: List of IDs from the files which are referenced in `data` or `meta` sections

The main difference is the second parameter named `parent_id` in the `addPage()` mutation and also returns:

```json
{
  "data": {
    "addPage": {
      "id": "10",
    }
  }
}
```

### Insert before page

To insert a new page before an existing one use:

```graphql
mutation {
  addPage(input: {
    lang: "en",
    path: "test-url-3",
    domain: "mydomain.tld",
    name: "Test page",
    title: "A Laravel CMS test page",
    to: "https://laravel-cms.org",
    tag: "test",
    meta: "{}",
    data: "[]",
    config: "{}",
    status: 0,
    cache: 5,
    start: "2023-04-01 00:00:00",
    end: null,
    content: ["0187d6ab-b76d-75ee-8830-ab00b4259aa5", "0187d6ab-b76d-75ee-8840-7e8026251ba0"],
    files: ["0187d6ab-b76d-75ee-8b0d-1b59cc3a1ab7"]
  }, parent: 1, ref: 2) {
    id
  }
}
```

Required parameters are:

input
: JSON object with key/value pairs for the [page properties](../graphql-datatypes.md#page-properties)

parent (optional)
: ID of the parent where the new page will be inserted below

ref (optional)
: ID of the page the new one will be inserted before

The `content` and `files` properties in the `input` parameter must be passed if shared content elements or files are referenced in the `data` or `meta` property:

content (list of IDs)
: List of IDs from the shared content elements which are referenced in `data` or `meta` sections

files (list of IDs)
: List of IDs from the files which are referenced in `data` or `meta` sections

The second parameter in the `addPage()` is still `parent_id` but followed by the ID of the page where the new pages should be inserted before. Like the other mutations it returns:

```json
{
  "data": {
    "addPage": {
      "id": "11",
    }
  }
}
```

## Move pages

### Make page a root page

To make an existing page a root page and append it at the end of the list of root pages use:

```graphql
mutation {
  movePage(id: 3) {
    id
    parent_id
  }
}
```

Required parameters are:

ID
: ID of the page to move to the root level

It will return:

```json
{
  "data": {
    "movePage": {
      "id": "3",
      "parent_id": null
    }
  }
}
```

### Move page to new parent

To move an existing page to a new parent page and append it at the end of the list of children use:

```graphql
mutation {
  movePage(id: 3, parent: 2) {
    id
    parent_id
  }
}
```

Required parameters are:

ID
: ID of the page to move to the root level

parent (optional)
: ID of the new parent page

The returned data will be:

```json
{
  "data": {
    "movePage": {
      "id": "3",
      "parent_id": "2"
    }
  }
}
```

### Move page before

To move an existing page before another page use:

```graphql
mutation {
  movePage(id: 3, parent: 1, ref: 5) {
    id
    parent_id
  }
}
```

Required parameters are:

ID
: ID of the page to move to the root level

parent (optional)
: ID of the new parent page

ref (optional)
: ID of the page the new one will be inserted before

The returned data will be:

```json
{
  "data": {
    "movePage": {
      "id": "3",
      "parent_id": "1"
    }
  }
}
```

## Save page properties

Saving page properties can be done using:

```graphql
mutation {
  savePage(id: 5, input: {
    lang: "en",
    path: "test-url-5",
    domain: "mydomain.tld",
    name: "Test page",
    title: "A Laravel CMS test page",
    to: "https://laravel-cms.org",
    tag: "test",
    meta: "{}",
    data: "[]",
    config: "{}",
    status: 0,
    cache: 5,
    start: "2023-04-01 00:00:00",
    end: null,
    content: ["0187d6ab-b76d-75ee-8830-ab00b4259aa5", "0187d6ab-b76d-75ee-8840-7e8026251ba0"],
    files: ["0187d6ab-b76d-75ee-8b0d-1b59cc3a1ab7"]
  }) {
    id
  }
}
```

Required parameters are:

ID
: ID of the page to store the changed properties for

input
: JSON object with key/value pairs for the [page properties](../graphql-datatypes.md#page-properties)

All properties are optional. The input parameter contains two additional parameters which must be passed if shared content elements or files are referenced in the `data` or `meta` property:

content (list of IDs)
: List of IDs from the shared content elements which are referenced in `data` or `meta` sections

files (list of IDs)
: List of IDs from the files which are referenced in `data` or `meta` sections

Note: Any value passed in `data` will create a new record in the version table for that data which can be published afterwards.

When your page meta data (`meta` field) or body data (`data` field) contains file references, you must pass their IDs using the `files` parameter. This ensures that the files are not deleted accidentially because no references exist.

This request will return:

```json
{
  "data": {
    "savePage": {
      "id": "5",
    }
  }
}
```

## Publish page meta data

Changes to the page meta data are stored as versions and you can publish the latest version using:

```graphql
mutation {
  pubPage(id: 1) {
    id
  }
}
```

Required parameters are:

ID
: ID of the page whose meta data should be published

The returned response will be:

```json
{
  "data": {
    "pubPage": {
      "id": "1",
    }
  }
}
```

## Delete and restore pages

### Trash pages

To trash a page and it's descendents so it can be restored use:

```graphql
mutation {
  dropPage(id: 7) {
    id
  }
}
```

Required parameters are:

ID
: ID of the page to delete

This request will return:

```json
{
  "data": {
    "dropPage": {
      "id": "7",
    }
  }
}
```

## Restore pages

A trashed page can be restored if it hasn't been pruned yet (configurable via `prune` setting in `./config/shop.php`). To restore a trashed page by it's ID use:

```graphql
mutation {
  keepPage(id: 7) {
    id
  }
}
```

Required parameters are:

ID
: ID of the page to restore

This request will return:

```json
{
  "data": {
    "keepPage": {
      "id": "7",
    }
  }
}
```

### Delete pages

To delete a page and it's descendents permanently use:

```graphql
mutation {
  dropPage(id: 7, force: true) {
    id
  }
}
```

Required parameters are:

ID
: ID of the page to delete permanently

This request will return:

```json
{
  "data": {
    "dropPage": {
      "id": "7",
    }
  }
}
```
