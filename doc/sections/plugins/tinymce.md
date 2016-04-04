Tinymce
======

You can expand tinymce and append your own button if you want. It has not been tested yet so you may run into several bugs, but this file should help you to correct them.

The form
-----------


The javascript
----------------

<script>tinymce.claroline.plugins.mypluginname = true; </script>

Inject your javascript in the layout by listening the "inject_javascript_layout" event
