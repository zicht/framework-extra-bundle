% Embed helper

The embed helper is used to embed controllers with forms as subrequests. Typically, you would do this with a `render`
inside twig, but you could also use this with esi's in varnish.

# Usage #

You call the handleForm() method on the embed helper service, like this:

~~~~php
<?php
public function myFormAction(Request $request)
{
    $form = $this->createForm(new MyType());

    return $this->getEmbedHelper()->handleForm(
        $form,
        $request,
        function($request, $form, $container) {
            // the form was posted and validated. Do something useful here.
            return true; // true indicates 'success' and the user may be redirected to the 'success_url', if available.
        },
        'my_route_name', // the route name to the current action
        array(
            // these parameters will be used to render the form url.
            // You can omit them if you don't need them.
        ),
        array(
            // These extra values will be passed to the template.
            // You can omit them if you don't need them.
        )
    );
}

public function getEmbedHelper()
{
    return $this->get('zicht_embed_helper');
}
~~~~

And inside the template:

~~~~html
<form method="post" action="{{ form_url }}">
    {{ form_widget(form) }}

    <input type="submit">
</form>
~~~~

Now, including this action within another template can be done as such:

~~~~
{{ render(controller('MyController:myForm', {'return_url': app.request.requestUri, 'success_url': '/done'} )) }}
~~~~

The template will be rendered, and the return_url and success_url parameters will be carried to the handle-request in
the form. If the form does not validate, the return_url is used, and the form errors will be stored in the session.
The next time the form is rendered, it will contain the errors, and thus shown to the user.

This makes it easily possible to reuse controllers with forms on different locations, without having to use different
handling controllers.