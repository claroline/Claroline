/**
 * Registers all plugins used by Claroline TinyMCE.
 */

// standard plugins
import 'tinymce/plugins/anchor'
import 'tinymce/plugins/advlist'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/autoresize'
import 'tinymce/plugins/charmap'
import 'tinymce/plugins/code'
import 'tinymce/plugins/contextmenu'
import 'tinymce/plugins/fullscreen'
import 'tinymce/plugins/image'
import 'tinymce/plugins/insertdatetime'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/media'
import 'tinymce/plugins/paste'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/searchreplace'
import 'tinymce/plugins/table'
import 'tinymce/plugins/textcolor'
import 'tinymce/plugins/visualblocks'
import 'tinymce/plugins/wordcount'

// claroline
import '#/main/core/tinymce/plugins/advanced-fullscreen'
import '#/main/core/tinymce/plugins/advanced-toolbar'
import '#/main/core/tinymce/plugins/codemirror'
import '#/main/core/tinymce/plugins/file-upload'
import '#/main/core/tinymce/plugins/mentions'
import '#/main/core/tinymce/plugins/resource-picker'

const plugins = [
  'advanced-fullscreen',
  'advanced-toolbar',
  'anchor',
  'autolink',
  'autoresize',
  'advlist',
  'charmap',
  'code',
  'codemirror',
  'contextmenu',
  'file-upload',
  'fullscreen',
  'image',
  'insertdatetime',
  'link',
  'lists',
  'media',
  'mentions',
  'paste',
  'preview',
  'resource-picker',
  'searchreplace',
  'table',
  'textcolor',
  'visualblocks',
  'wordcount'
]

export {
  plugins
}
