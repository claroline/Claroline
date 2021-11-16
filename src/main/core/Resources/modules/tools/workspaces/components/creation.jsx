import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceForm} from '#/main/core/workspace/components/form'

const WorkspaceCreation = (props) =>
  <WorkspaceForm
    level={3}
    name="workspaces.creation"
    meta={false}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.save().then(workspace =>
        props.history.push(route(workspace))
      )
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
  />

WorkspaceCreation.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  path: T.string.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  save: T.func.isRequired
}

WorkspaceCreation.defaultProps = {
  workspace: WorkspaceTypes.defaultProps
}

export {
  WorkspaceCreation
}
