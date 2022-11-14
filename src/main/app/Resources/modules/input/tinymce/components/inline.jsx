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
    //initialValue={props.initialValue}
    inline={true}
    init={merge({}, config, {
      selector: props.id,
      placeholder: props.placeholder,

      toolbar: false,
      menubar: false,

      // plugin autoresize
      // FIXME this does not work in inline mode
      plugins: ['autoresize'],
      min_height: `${props.minRows * 34}px`,
      max_height: 500
    }, props.config || {}, {
      // give access to the show modal action to tinymce plugins
      // it's not really aesthetic but there is no other way
      showModal: props.showModal,
      // get the current workspace for the file upload and resource explorer plugins
      workspace: props.workspace
    })}
    onEditorChange={(v) => {
      console.log(v)
      //console.log(v)
      if (v !== props.value) {
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
  minRows: T.number,

  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  showModal: T.func.isRequired
}

const TinymceInline = withModal(TinymceEditor)

export {
  TinymceInline
}
