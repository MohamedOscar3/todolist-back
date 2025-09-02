# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

You can obtain your API token by registering a new user with the `/api/auth/register` endpoint or logging in with the `/api/auth/login` endpoint. The token will be included in the response.
