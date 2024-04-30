import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContextEditor} from '#/main/app/context/editor/containers/main'

import {EditorOverview} from '#/main/app/contexts/workspace/editor/components/overview'

const WorkspaceEditor = (props) =>
  <ContextEditor
    overview={EditorOverview}
    actions={[
      {
        name: 'delete',
        label: trans('delete', 'actions'),
        type: CALLBACK_BUTTON,
        callback: () => true
      }
    ]}
  />

WorkspaceEditor.propTypes = {

}

export {
  WorkspaceEditor
}
