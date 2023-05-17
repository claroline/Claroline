import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Editor} from '@tinymce/tinymce-react'
import merge from 'lodash/merge'

import {withModal} from '#/main/app/overlays/modal'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {config} from '#/main/app/input/tinymce/config'

const TinymceEditor = (props) =>
  <Editor
    id={props.id}
    disabled={props.disabled}
    value={props.value}
    initialValue={props.initialValue}
    inline={false}
    init={merge({}, config, {
      selector: props.id,
      placeholder: props.placeholder,
      auto_focus: props.id,
      height: '100%',
      menubar: 'edit view insert format help',
      menu: {
        view: {
          title: 'View',
          items: 'wordcount | visualaid visualchars visualblocks | preview code'
        },
        insert: {
          title: 'Insert',
          items: 'resource-picker file-upload placeholders | image link media template inserttable | charmap emoticons hr codesample | insertdatetime'
        }
      },
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough ' +
        '| forecolor backcolor removeformat | alignleft aligncenter alignright alignjustify | outdent indent ' +
        '| numlist bullist | link resource-picker file-upload placeholders insertfile image media table'
    }, props.config, {
      // give access to the show modal action to tinymce plugins
      // it's not really aesthetic but there is no other way
      showModal: props.showModal,
      // get the current workspace for the file upload and resource explorer plugins
      workspace: props.workspace
    })}
    onEditorChange={(v) => {
      console.log('coucou')
      if (v !== props.value) {
        console.log(v)
        props.onChange(v)
      }
    }}
  />

TinymceEditor.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  value: T.string,
  initialValue: T.string,
  placeholder: T.string,
  config: T.object,
  onChange: T.func,

  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  showModal: T.func.isRequired
}

const TinymceFull = withModal(TinymceEditor)

export {
  TinymceFull
}
