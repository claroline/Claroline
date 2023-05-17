import {locale} from '#/main/app/intl'
import {asset, param} from '#/main/app/config'
import {theme} from '#/main/theme/config'

//import tinymce from 'tinymce/tinymce'
import '#/main/app/input/tinymce/plugins'
import '#/main/app/input/tinymce/langs'

/**
 * Common configuration for all of our tinymce instances.
 * It is customized depending on the mode you choose (minimal, classic, full).
 */
const config = {
  language: locale(),
  base_url: asset('packages/tinymce'),
  // convert all relatives URLs into absolute ones
  // this is required for templates to work
  relative_urls: false,

  setup: (editor) => {
    editor.on('change', () =>  {
      console.log('change')
      editor.save()
    })

    //console.log(editor.isDirty())
    /*console.log('setup')
    editor.save()
    editor.on('change', () => editor.save())*/

    editor.on('init', function () {
      editor.save()
    })
  },

  // styles
  skin: null, // we provide it through theme system
  content_css: [
    theme('bootstrap')
  ],

  // plugins
  plugins: [
    'autolink',
    'charmap',
    'code',
    'codesample',
    'emoticons',
    'help',
    'image',
    'insertdatetime',
    'link',
    'advlist',
    'lists',
    'media',
    'preview',
    'quickbars',
    'searchreplace',
    'table',
    //'template',
    'visualblocks',
    'visualchars',
    'wordcount',

    // claroline plugins
    'file-upload',
    'resource-picker'
  ],

  browser_spellcheck: true,
  // filter HTML elements
  extended_valid_elements: 'user[id], a[data-toggle|data-parent], span[*]',
  invalid_elements : param('richTextScript') ? undefined : 'script',

  // toolbars config
  statusbar: false,
  branding: false,
  promotion: false,
  contextmenu: 'resource-picker file-upload placeholders | link image media inserttable | charmap emoticons hr | insertdatetime',
  link_context_toolbar: true,

  // add more font size (default stops at 36pt)
  font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt 60pt 72pt 96pt',

  // plugins config
  quickbars_selection_toolbar: 'quicklink | blocks | bold italic underline forecolor | removeformat',
  quickbars_image_toolbar: 'image | alignleft aligncenter alignright',
  quickbars_insert_toolbar: false,
  //quickbars_insert_toolbar: 'link resource-picker file-upload | insertfile image media table'

  table_toolbar: 'tableprops tabledelete | tablerowprops tableinsertrowbefore tableinsertrowafter tabledeleterow | tablecellprops tableinsertcolbefore tableinsertcolafter tabledeletecol'
}

export {
  config
}