import {plugins} from '#/main/core/tinymce/plugins'
import '#/main/core/tinymce/langs'
import '#/main/core/tinymce/themes'

import {locale} from '#/main/core/intl/locale'
import {platformConfig} from '#/main/core/platform'
import {asset, theme} from '#/main/core/scaffolding/asset'

const config = {
  //TODO: this is for retro comp purpose
  setup: (editor) => editor.on('change', () => editor.save()),
  language: locale(),
  theme: 'modern',
  skin: false, // we provide it through theme system
  content_css: [
    // reuse current platform theme for content
    theme()
  ],
  menubar: true,
  statusbar: true,
  branding: false,
  resize: true,

  // enabled plugins
  plugins: plugins,

  // plugin : autoresize
  autoresize_min_height: 160,
  autoresize_max_height: 500,

  //allow to fetch tinymce plugins
  baseURL: asset('packages/tinymce'),
  relative_urls : false,

  // plugin : paste
  paste_data_images: true,
  paste_preprocess: (plugin, args) => {
    if (platformConfig('openGraph.enabled') && args.content) {
      // todo check if url
      const link = args.content.trim()

      window.Claroline.Home.canGenerateContent(link, function (data) {
        args.content = '<div class="url-content">' + data + '</div>'
      })
    }
  },

  // plugin : insertdatetime
  insertdatetime_formats: [ // todo configure
    '%H:%M:%S',
    '%Y-%m-%d',
    '%I:%M:%S %p',
    '%D'
  ],

  // plugin : codemirror
  codemirror: {
    // todo : find a way to reuse our instance of codemirror
    path: asset('packages/tinymce-codemirror/plugins/codemirror/codemirror-4.8')
  },

  extended_valid_elements: 'user[id], a[data-toggle|data-parent], span[*]',
  remove_script_host: false,
  browser_spellcheck: true,

  // toolbars & buttons
  insert_button_items: 'resource-picker file-upload link media image | anchor charmap inserttable insertdatetime',
  toolbar1: 'advanced-toolbar | insert | undo redo | formatselect | bold italic forecolor | fullscreen',
  toolbar2: 'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat preview code'
}

export {
  config
}
