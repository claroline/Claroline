import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/main/core/workspace/constants'
import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {ContextMenu} from '#/main/app/context/containers/menu'

const WorkspaceMenu = (props) =>
  <ContextMenu
    title={
      <>
        {!isEmpty(props.workspace) ? props.workspace.name : trans('workspace')}
        <small className="text-truncate">
          {!isEmpty(props.roles) ?
            props.roles.map(role => trans(role.translationKey)).join(', ') :
            trans('guest')
          }
        </small>
      </>
    }

    tools={props.tools
      // hide tools that can not be configured in models for now
      .filter(tool => !get(props.workspace, 'meta.model', false) || -1 !== constants.WORKSPACE_MODEL_TOOLS.indexOf(tool.name))
    }
  />

WorkspaceMenu.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  impersonated: T.bool.isRequired,
  userEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

WorkspaceMenu.defaultProps = {
  workspace: {}
}

export {
  WorkspaceMenu
}
