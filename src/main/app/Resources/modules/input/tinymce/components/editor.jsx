import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {TinymceFull} from '#/main/app/input/tinymce/components/full'
import {TinymceInline} from '#/main/app/input/tinymce/components/inline'
import {TinymceClassic} from '#/main/app/input/tinymce/components/classic'

const TinymceEditor = (props) => {
  const [initialValue, updateInitialValue] = useState(props.initialValue || props.value || '')

  switch (props.mode) {
    case 'full':
      return (
        <TinymceFull {...props} initialValue={initialValue} />
      )
    case 'inline':
      return (
        <TinymceInline {...props} initialValue={initialValue} />
      )
    case 'classic':
      return (
        <TinymceClassic {...props} initialValue={initialValue} />
      )
  }
}

TinymceEditor.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  value: T.string,
  placeholder: T.string,
  mode: T.oneOf(['inline', 'classic', 'full']),
  onChange: T.func,
  minRows: T.number,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  )
}

TinymceEditor.defaultProps = {
  mode: 'classic',
  value: ''
}

export {
  TinymceEditor
}
