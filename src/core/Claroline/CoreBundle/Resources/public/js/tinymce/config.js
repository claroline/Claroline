// Create a new plugin class
tinymce.create('tinymce.plugins.ExamplePlugin', {
    init : function (ed, url) {
        // Register an example button
        ed.addButton('ressourceLinker', {
            title : 'ressourceLinker',
            image : 'http://stfalcon.com/favicon.ico',
            onclick : function () {
                Claroline.ResourceManager.picker('open');
            },
            'class' : 'bold' // Use the bold icon from the theme
        });
    }
});

// Register plugin with a short name
tinymce.PluginManager.add('ressourceLinker', tinymce.plugins.ExamplePlugin);
tinymce.activeEditor.setContent('');