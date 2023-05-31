import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {withModal} from '#/main/app/overlays/modal'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {config} from '#/main/app/input/tinymce/config'
import {TinymceFull} from '#/main/app/input/tinymce/components/full'
import {TinymceInline} from '#/main/app/input/tinymce/components/inline'
import {TinymceClassic} from '#/main/app/input/tinymce/components/classic'

/**
 * ATTENTION : internal state is used because of :
 *   > The onEditorChange prop is used to provide an event handler that will be run when any change is made to the editor content.
 *   > Changes to the editor must be applied to the value prop within 200 milliseconds to prevent the changes being rolled back.
 *
 * When the editor is mounted in our form, the data round trip to the redux store then back to the editor component
 * may take more than 200ms and the editor rollback itself loosing some changes.
 *
 * @see https://www.tiny.cloud/docs/tinymce/6/react-ref/#using-the-tinymce-react-component-as-a-controlled-component
 */
const Tinymce = (props) => {
  const [value, setValue] = useState(props.initialValue || props.value || '')
  useEffect(() => setValue(props.initialValue || ''), [props.initialValue])
  useEffect(() => setValue(props.value || ''), [props.value])

  const editorProps = merge({}, omit(props, 'onChange', 'placeholder'), {
    value: value,
    onEditorChange: (v) => {
      if (v !== value) {
        // store value locally to directly update tinymce state
        setValue(v)
        // propagate change to the parents
        props.onChange(v)
      }
    },
    onSelectionChange: props.onSelect,
    init: merge({
      selector: props.id,
      placeholder: props.placeholder,
      // give access to the show modal action to tinymce plugins
      // it's not really aesthetic but there is no other way
      showModal: props.showModal,
      // get the current workspace for the file upload and resource explorer plugins
      workspace: props.workspace
    }, config, props.config || {})
  })

  switch (props.mode) {
    case 'full':
      return (
        <TinymceFull {...editorProps} />
      )
    case 'inline':
      return (
        <TinymceInline {...editorProps} />
      )
    case 'classic':
      return (
        <TinymceClassic {...editorProps} />
      )
  }
}

Tinymce.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  value: T.string,
  initialValue: T.string,
  placeholder: T.string,
  mode: T.oneOf(['inline', 'classic', 'full']),
  /**
   * A custom configuration for the editor (It is merged with the base tinymce config + the selected mode config)
   */
  config: T.object,
  onChange: T.func,
  onSelect: T.func,
  minRows: T.number,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),

  // from store
  showModal: T.func.isRequired
}

Tinymce.defaultProps = {
  mode: 'classic',
  value: ''
}

const TinymceEditor = withModal(Tinymce)

export {
  TinymceEditor
}
