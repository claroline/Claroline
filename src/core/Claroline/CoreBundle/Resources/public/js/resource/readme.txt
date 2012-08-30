Requirement for writing resources templates view.

BreadCrum:
in the loop {% foreach parent in parents %}
Each breadCrum element must
- be defined in an HTML element from the class "{{ prefix }}-breadcrum-link"
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

Menu:
    left click menu will be defined for each object of the class "{{ prefix }}-resource-menu-left"
    right click menu will be defined for each object of the class "{{ prefix }}-resource-menu-right"
    a menu must be defined in a .{{ prefix }}-res-block span



