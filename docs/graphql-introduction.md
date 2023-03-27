---
title: "Introduction"
permalink: /graphql/introduction/
excerpt: "How to manage data in Laravel CMS using the GraphQL API"
---

The Laravel CMS GraphQL backendend API follows the GraphQL standard documented at [graphql.org](https://graphql.org) and is available at (replace "mydomain.tld" with your own one):

```
http://mydomain.tld/graphql
```

* [Authentication](#authentication)
* [Retrieve data](#retrieve-data)
  * [Single query](#single-query)
  * [Batched queries](#batched-queries)
* [Modify data](#modify-data)
  * [Single mutation](#single-mutation)
  * [Batched mutations](#batched-mutations)

# Authentication

For authentication, an valid user record with a known password must exist in the Laravel `users` table. Also, the user record must be authorized as CMS editor, which can be done by executing the `artisan` command on the command line:

```bash
php artisan cms:editor editor@example.com
```

Then, you can use the `cmsLogin` GraphQL mutation to authenticate the user with his e-mail and password:

```javascript
const body = JSON.stringify({'query':
`mutation {
  cmsLogin(email: "editor@example.com", password: "secret") {
    name
    email
  }
}`});

fetch('http://mydomain.tld/graphql', {
	method: 'POST',
	credentials: 'include',
	body: body
}).then(response => {
	return response.json();
}).then(data => {
	console.log(data);
});
```

The response will include the name and e-mail addres of the user if the authentication was successful or an error in case the authentication failed:

```json
{
  "data": {
    "cmsLogin": {
      "name": "A CMS editor",
      "email": "editor@example.com"
    }
  }
}
```

For more details, please have a look at the [Laravel CMS GraphQL authentication article](graphql-authentication.md).

# Retrieve data

Note: You must authenticate first to use the GraphQL API!

## Single query

GraphQL uses **query** POST requests to retrieve data from the server. To retrieve a list of pages you can use code like this:

```javascript
const body = JSON.stringify({'query':
`query {
  pages {
    id
    name
    title
    status
    slug
  }
}`});

fetch('http://mydomain.tld/graphql', {
	method: 'POST',
	credentials: 'include',
	body: body
}).then(response => {
	return response.json();
}).then(data => {
	console.log(data);
});
```

The response is a JSON data structure with the requested data:

```json
{
  "data": {
    "pages": [
      {
        "id": "1",
        "name": "Home",
        "title": "Home | Laravel CMS",
        "status": 1,
        "slug": "",
      }
    ]
  }
}
```

Please have a look into articles for the different resources for more details.

## Batched queries

You can use several queries on one request to reduce the number of requests:

```graphql
query {
  me {
    name
  }
  pages {
    id
    name
    title
    status
    slug
  }
}
```

The result is a batched query with a response like this one:

```json
{
  "data": {
    "me": {
        "name": "me@mydomain.tld"
    },
    "pages": [
      {
        "id": "1",
        "name": "Home",
        "title": "Home | Laravel CMS",
        "status": 1,
        "slug": "",
      }
    ]
  }
}
```

# Modify data

Note: You must authenticate first to use the GraphQL API!

## Single mutation

Similar to query requests, GraphQL uses **mutation** POST requests to add, update and delete data at the server. To add a new page you can use code like this:

```javascript
const body = JSON.stringify({'query':
`mutation {
  addPage(input: {
    lang: ""
    domain: ""
    slug: "test-url"
    name: "Test page"
    title: "Test page | Laravel CMS"
    to: ""
    tag: "root"
    data: "{}"
    config: "{}"
    status: 1
    cache: 5
  }) {
    id
  }
}`});

fetch('http://mydomain.tld/graphql', {
	method: 'POST',
	credentials: 'include',
	body: body
}).then(response => {
	return response.json();
}).then(data => {
	console.log(data);
});
```

The response is a JSON data structure with the requested data:

```json
{
  "data": {
    "addPage": {
      "id": "10"
    }
  }
}
```

Please have a look into articles for the different resources for more details.

## Batched mutations

You can use several mutations on one request to reduce the number of requests:

```graphql
mutation {
  addPage(input: {
    lang: ""
    domain: ""
    slug: "test-url"
    name: "Test page"
    title: "Test page | Laravel CMS"
    to: ""
    tag: "root"
    data: "{}"
    config: "{}"
    status: 1
    cache: 5
  }) {
    id
  }
  deletePage(id: "1")
}
```

The result is a batched query with a response like this one:

```json
{
  "data": {
    "addPage": {
      "id": "11"
    },
    "deletePage": "1"
  }
}
```
