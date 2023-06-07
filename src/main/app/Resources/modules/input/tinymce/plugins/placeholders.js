import {trans} from '#/main/app/intl/translation'

const pluginName = 'placeholders'

const dialogConfig = (editor, placeholders = []) => ({
  title: 'Insert dynamic value',
  body: {
    type: 'panel',
    items: [
      {
        type: 'htmlpanel',
        html: trans('placeholders_info', {}, 'template')
      }, {
        type: 'htmlpanel',
        html: '<div class="tox-collection--list"><div class="tox-collection__group">'+
          placeholders.reduce((acc, placeholder) => acc + (
            `<a href="#" class="tox-collection__item" onclick="() => editor.execCommand('mceInsertContent', false, '%'+placeholder+'%')">
              <div class="tox-collection__item-label">
                <b>%${placeholder}%</b>
                <p class="text-muted">${trans(`${placeholder}_desc`, {}, 'template')}</p>
              </div>
            </a>`
          ), '')+'</div></div>'
      }
    ]
  },
  buttons: [
    {
      type: 'cancel',
      name: 'closeButton',
      text: 'Close',
      buttonType: 'primary'
    }
  ],
  onSubmit: (api) => {
    //editor.execCommand('mceInsertContent', false, `%${placeholder}%`)

    api.close()
  }
})

// Register new plugin
window.tinymce.PluginManager.add(pluginName, (editor) => {
  // register new option in editor config to retrieve the list of available placeholders
  editor.options.register('placeholders', {
    processor: 'array'
  })

  // get registered placeholders
  const placeholders = editor.options.get('placeholders')

  // provides an insert menu item to open a dialog to insert one of the defined placeholder
  editor.ui.registry.addMenuItem(pluginName, {
    icon: 'template',
    text: 'Dynamic value...',
    onAction: () => editor.windowManager.open(dialogConfig(editor, placeholders))
  })

  // provides a toolbar button to open a dialog to insert one of the defined placeholder
  editor.ui.registry.addButton(pluginName, {
    icon: 'template',
    tooltip: 'Insert dynamic value',
    onAction: () => editor.windowManager.open(dialogConfig(editor, placeholders))
  })

  // provides autocomplete
  editor.ui.registry.addAutocompleter(pluginName, {
    trigger: '%',
    columns: 1,
    highlightOn: ['placeholder_name'],
    fetch: (pattern) => Promise.resolve(placeholders
      .filter(placeholder => -1 !== placeholder.indexOf(pattern))
      .map(placeholder => ({
        type: 'cardmenuitem',
        value: placeholder,
        label: placeholder,
        items: [
          {
            type: 'cardcontainer',
            direction: 'vertical',
            items: [
              {
                type: 'cardtext',
                text: `%${placeholder}%`,
                name: 'placeholder_name',
                classes: ['h5']
              }, {
                type: 'cardtext',
                text: trans(`${placeholder}_desc`, {}, 'template'),
                classes: ['text-muted']
              }
            ]
          }
        ]
      }))
    ),
    onAction: (autocompleteApi, rng, value) => {
      editor.selection.setRng(rng)
      editor.insertContent(`%${value}%`)

      autocompleteApi.hide()
    }
  })
})
