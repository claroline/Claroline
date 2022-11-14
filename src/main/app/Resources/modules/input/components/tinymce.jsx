import React, {useRef} from 'react'
import {PropTypes as T} from 'prop-types'
import {Editor} from '@tinymce/tinymce-react'
import {asset} from '#/main/app/config'

//import {tinymce as originalTinymce} from 'tinymce/tinymce'

// disabled menubar : file
// disabled plugins : template directionality autosave save
// disabled buttons : ltr rtl save print
const fullConfig = {
  height: '100%',
  plugins: 'preview importcss searchreplace autolink ' +
    'code visualblocks visualchars fullscreen image link media codesample table ' +
    'charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
  //editimage_cors_hosts: ['picsum.photos'],
  menubar: 'edit view insert format tools table help',
  toolbar: 'undo redo | bold italic underline strikethrough ' +
    '| fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent ' +
    '| numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons ' +
    '| fullscreen  preview  | insertfile image media table link anchor codesample',
  toolbar_sticky: true,
  //toolbar_sticky_offset: isSmallScreen ? 102 : 108,
  /*autosave_ask_before_unload: true,
  autosave_interval: '30s',
  autosave_prefix: '{path}{query}-{id}-',
  autosave_restore_when_empty: false,
  autosave_retention: '2m',*/
  image_advtab: true,
  link_list: [
    { title: 'My page 1', value: 'https://www.tiny.cloud' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'https://www.tiny.cloud' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  file_picker_callback: (callback, value, meta) => {
    /* Provide file and text for the link dialog */
    if (meta.filetype === 'file') {
      callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
    }

    /* Provide image and alt text for the image dialog */
    if (meta.filetype === 'image') {
      callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
    }

    /* Provide alternative source and posted for the media dialog */
    if (meta.filetype === 'media') {
      callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
    }
  },
  /*templates: [
    { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',*/
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
  noneditable_class: 'mceNonEditable',
  toolbar_mode: 'sliding',
  contextmenu: 'link image media table',
  skin: 'oxide',
  content_css: 'default',
  /*skin: useDarkMode ? 'oxide-dark' : 'oxide',
  content_css: useDarkMode ? 'dark' : 'default',*/
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
  statusbar: false,
  branding: false,
  promotion: false
}

const normalConfig = {
  //skin: false, // we provide it through theme system
  // plugin autoresize
  autoresize_min_height: '160px',//`${props.minRows * 34}px`,
  //autoresize_max_height: 500,
  //height: `${props.minRows * 34}px`,
  menubar: false,
  statusbar: false,
  branding: false,
  plugins: [
    'autoresize', 'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'code', 'help'
  ],
  toolbar: 'undo redo | blocks | ' +
    'bold italic forecolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | fullscreen preview code help',
  //content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
}

const Tinymce = (props) => {
  const editorRef = useRef(null);

  return (
    <Editor
      id={props.id}
      disabled={props.disabled}
      value={props.value}
      inline={props.inline}
      init={'full' === props.mode ? fullConfig : normalConfig}
      onInit={(evt, editor) => editorRef.current = editor}
      onEditorChange={props.onChange}
      onSelectionChange={() => 'coucou'}
      tinymceScriptSrc={asset('packages/tinymce/tinymce.min.js')}
    />
  )
}

Tinymce.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  mode: T.oneOf(['normal', 'full']),
  value: T.string,
  onChange: T.func,
  inline: T.bool,
  minRows: T.number
}

Tinymce.defaultProps = {
  inline: false,
  mode: 'normal'
}

export {
  Tinymce
}
