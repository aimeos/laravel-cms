
Laravel CMS tries to authenticate against entries of the Laravel `users` table. To be able to use the GraphQL API, they need to be editors (use `php artisan cms:editor editor@example.com` to set the editor role).

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

To log out the current user:

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
