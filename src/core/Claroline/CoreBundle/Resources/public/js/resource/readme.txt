Requirements for writing resources templates views
--------------------------------------------------

The twigjs templates are loaded by the twig template
src\core\Claroline\CoreBundle\Resources\views\Dashboard\resources.html.twig:
    {% javascripts
       vars=["locale"]
      "@ClarolineCoreBundle/Resources/views/Resource/resource_thumbnail.html.twigjs"
      "@ClarolineCoreBundle/Resources/views/Resource/resource_list.html.twigjs"
       filter="twig_js"
    %}
You must add your new twigjs template in this place.

The templates are given to the ClaroRessourceGetter in the file
src\core\Claroline\CoreBundle\Resources\public\js\dashboard\dashboard_resource.js:
var resourceGetter = new ClaroResourceGetter.getter(resource_thumbnail_template, resource_list_template);
The name to provide is the name set in the twigjs template (at first line).
The ClaroRessourceGetter need two templates: the first one well be used to display resources in "thumbnails" view
and the second one will be used to display resources in "list" view (no thumbnail, just a list of filenames).

The template should render three parts: the breadcrumb, the list of ressources and their linked menu.

BreadCrumb:
    In the loop {% foreach parent in parents %} each breadcrumb element must
        - be defined in an HTML element from the class "{{ prefix }}-breadcrumb-link"
        - have the data-id="{{ parent.id }}" attribute defined

Resource:
    in the loop {% foreach instance as instances %}
    Each resource must be rendered in a <span class='{{ prefix }}-res-block'> This block must have the following data-attributes:
        data-id="{{ instance.id }}"
        data-type="{{ instance.type }}"
        data-resource-id="{{ instance.resource_id }}"
        Navigation:
            must be defined in this span
            is a link from the class "{{ prefix }}-link-navigate-instance"
            Once a user clicks on the link, the page will be refreshed according to the data-id defined in the span.
        Selection:
            add this line in the resource span
            <input class="{{ prefix }}-chk-instance"type="checkbox" value="{{ instance.id }}">
        Thumbnails:
            use "{{webRoot}}/{{ instance.large_icon}}" to get the large_icon url
            use "{{webRoot}}/{{ instance.small_icon}}" to get the small_icon url

Warning: if you don't have the watchr running, please run "app/console assetic:dump" to generate
the js file from the twigjs file.

Menu:
    left click menu will be defined for each object of the class "{{ prefix }}-resource-menu-left"
    right click menu will be defined for each object of the class "{{ prefix }}-resource-menu-right"
    a menu must be defined in a .{{ prefix }}-res-block span



