import {locale} from '#/main/app/intl'
import {asset, param} from '#/main/app/config'
import {isDarkMode, theme} from '#/main/theme/config'

import '#/main/app/input/tinymce/plugins'
import '#/main/app/input/tinymce/langs'

/**
 * Common configuration for all of our tinymce instances.
 * It is customized depending on the mode you choose (minimal, classic, full).
 */
const config = {
  language: 'fr' === locale() ? 'fr_FR' : locale(),
  base_url: asset('packages/tinymce'),
  // convert all relatives URLs into absolute ones
  // this is required for templates to work
  relative_urls: false,

  body_id: isDarkMode() ? 'data-bs-theme="dark"' : 'data-bs-theme="light"',

  // styles
  skin: isDarkMode() ? 'oxide-dark' : 'oxide', // we provide it through theme system
  content_css: [
    theme('bootstrap')
  ].concat(isDarkMode() ? ['dark'] : []),
  highlight_on_focus: false,
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
    'visualblocks',
    'visualchars',
    'wordcount',
    // claroline plugins
    'file',
    'resource-picker',
    'formula'
  ],

  browser_spellcheck: true,
  // filter HTML elements
  extended_valid_elements: 'user[id], a[data-toggle|data-parent], span[*]',
  invalid_elements : param('richTextScript') ? undefined : 'script',

  // toolbars config
  statusbar: false,
  branding: false,
  promotion: false,
  contextmenu: 'resource-picker file placeholders | link image media inserttable | formula charmap emoticons hr | insertdatetime',
  link_context_toolbar: true,

  // add more font size (default stops at 36pt)
  font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt 60pt 72pt 96pt',

  block_formats: [
    'Heading 3=h3',
    'Heading 4=h4',
    'Heading 5=h5',
    'Heading 6=h6',
    'Paragraph=p',
    'Emphasis=custom-lead',
    'Card=custom-card',
    'Box=custom-box',
    'Alert primary=custom-alert-primary',
    'Alert secondary=custom-alert-secondary',
    'Button primary=custom-btn-primary'
  ].join(';'),
  // adds custom formats using theme styles (those are not rendered in PDF prints for now)
  style_formats: [
    { title: 'Headings', items: [
      { title: 'Heading 3', format: 'h3' },
      { title: 'Heading 4', format: 'h4' },
      { title: 'Heading 5', format: 'h5' },
      { title: 'Heading 6', format: 'h6' }
    ]},
    { title: 'Blocks', items: [
      { title: 'Paragraph', format: 'p' },
      { name: 'lead', title: 'Emphasis', block: 'p', classes: [ 'lead text-body-secondary rounded-3' ] },
      { name: 'box', title: 'Box', block: 'div', classes: [ 'p-4 bg-body-tertiary rounded-3' ], wrapper: true },
      { name: 'card', title: 'Card', block: 'div', classes: [ 'p-4 bg-body rounded-3 border shadow-sm' ], wrapper: true },
      { title: 'Blockquote', format: 'blockquote' },
      { title: 'Div', format: 'div' },
      { title: 'Pre', format: 'pre' },
      { title: 'Address', format: 'address' }
    ]},
    { title: 'Alerts', items: [
      { name: 'alert-primary', title: 'Alert primary', block: 'div', classes: [ 'alert alert-primary' ], wrapper: true },
      { name: 'alert-secondary', title: 'Alert secondary', block: 'div', classes: [ 'alert alert-secondary' ], wrapper: true },
      { name: 'alert-success', title: 'Alert success', block: 'div', classes: [ 'alert alert-success' ], wrapper: true },
      { name: 'alert-info', title: 'Alert info', block: 'div', classes: [ 'alert alert-info' ], wrapper: true },
      { name: 'alert-warning', title: 'Alert warning', block: 'div', classes: [ 'alert alert-warning' ], wrapper: true },
      { name: 'alert-danger', title: 'Alert danger', block: 'div', classes: [ 'alert alert-danger' ], wrapper: true }
    ]},
    { title: 'Buttons', items: [
      { name: 'btn-primary', title: 'Button primary', selector: 'a,button', classes: 'btn btn-primary', remove: 'empty'},
      { name: 'btn-body', title: 'Button body', selector: 'a,button', classes: 'btn btn-body', remove: 'empty'},
      { name: 'btn-link', title: 'Button link', selector: 'a,button', classes: 'btn btn-link', remove: 'empty'},
      { name: 'btn-secondary', title: 'Button secondary', selector: 'a,button', classes: 'btn btn-secondary', remove: 'empty'},
      { name: 'btn-info', title: 'Button info', selector: 'a,button', classes: 'btn btn-info', remove: 'empty'},
      { name: 'btn-success', title: 'Button success', selector: 'a,button', classes: 'btn btn-success', remove: 'empty'},
      { name: 'btn-warning', title: 'Button warning', selector: 'a,button', classes: 'btn btn-warning', remove: 'empty'},
      { name: 'btn-danger', title: 'Button danger', selector: 'a,button', classes: 'btn btn-danger', remove: 'empty'}
    ]}
  ],
  style_formats_autohide: true,
  // The following option is used to append style formats rather than overwrite the default style formats.
  style_formats_merge: false,
  sandbox_iframes: false,

  // plugins config
  quickbars_selection_toolbar: 'quicklink | blocks | bold italic underline forecolor align | removeformat',
  quickbars_image_toolbar: 'image | alignleft aligncenter alignright',
  quickbars_insert_toolbar: false,
  //quickbars_insert_toolbar: 'link resource-picker file-upload | insertfile image media table'

  table_toolbar: 'tableprops tabledelete | tablerowprops tableinsertrowbefore tableinsertrowafter tabledeleterow | tablecellprops tableinsertcolbefore tableinsertcolafter tabledeletecol'
}

export {
  config
}
