---
title: "Authentication"
permalink: /graphql/authentication/
excerpt: "How to authenticate at Laravel CMS using the GraphQL API"
---

* [Login](#login)
* [Retrieve user](#retrieve-user)
* [Logout](#logout)

Laravel CMS tries to authenticate against entries of the Laravel `users` table. To be able to use the GraphQL API, they need to be editors (use the `artisan` command to set the editor role):

```bash
php artisan cms:editor editor@example.com
```

## Login

To authenticate for editing content:

```graphql
mutation {
  cmsLogin(email: "editor@example.com", password: "secret") {
    name
    email
  }
}
```

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

## Retrieve user

Retrieve information about the authenticated user:

```graphql
query {
  me {
    name
    email
  }
}
```

```json
{
  "data": {
    "me": {
      "name": "A CMS editor",
      "email": "editor@example.com"
    }
  }
}
```

## Logout

To log the current user out of the application:

```graphql
mutation {
  cmsLogout {
    name
    email
  }
}
```

```json
{
  "data": {
    "cmsLogout": {
      "name": "A CMS editor",
      "email": "editor@example.com"
    }
  }
}
```
