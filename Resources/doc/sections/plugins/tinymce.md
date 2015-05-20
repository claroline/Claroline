Tinymce
======

You can expand tinymce and append your own button if you want. It has not been tested yet so you may run into several bugs, but this file should help you to correct them.

The form
-----------

There is a custom tinymce field located in Claroline\CoreBundle\Form\Field\TinymceType.
As you can see, you can set add your own button with the form attr/data-custom-buttons element. This is a list of space separated value (like the class one).

When using the tinymce field in your form, you should be able to simply override the 'attr' array.

The javascript
----------------

Create a new javascript file (don't forget to load it).
You can define the javascript for your button using:

    window.tinymce.claroline.buttons.myButtonName = function(editor) {
		editor.addButton('btnName', {
			...
		}
    }

where "myButtonName" is the name you must add in the data-custom-buttons element.

see Claroline/CoreBundle/Resources/public/js/tinymce/resource_picker_button.js for a working example.
