## Url helpers ##

From the `UrlCheckerService` file:

    /**
     * Provides a checker to determine if a url is safe
     *
     * By default the service will consider url's safe when they are either:
     * a) relative urls, or
     * b) absolute urls within the same domain, i.e. zicht.nl and www.zicht.nl are in the same domain.
     *
     * Alternatively the user can specify their own array with regular expressions to
     * determine whether a url is safe.
     *
     * Example:
     * ```php
     * // Redirect to success_url when the user is already logged in
     * if ($this->isUserLoggedIn()) {
     *    throw new UrlFlowException(
     *       'User is already logged in',
     *       $this->get(UrlCheckerService::class)->getSafeUrl($request->get('success_url'), '/fallback-url')
     *    );
     * }
     */
