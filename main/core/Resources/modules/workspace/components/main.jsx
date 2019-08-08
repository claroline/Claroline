import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {Routes} from '#/main/app/router'
import {ContentLoader} from '#/main/app/content/components/loader'

import {ToolMain} from '#/main/core/tool/containers/main'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceRestrictions} from '#/main/core/workspace/components/restrictions'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

const WorkspaceMain = (props) => {
  if (!props.loaded) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre espace d'activités"
      />
    )
  }

  if (!isEmpty(props.accessErrors)) {
    return (
      <WorkspaceRestrictions
        errors={props.accessErrors}
        dismiss={props.dismissRestrictions}
        managed={props.managed}
      />
    )
  }

  if (!isEmpty(props.workspace)) {
    return (
      <Routes
        path={workspaceRoute(props.workspace)}
        routes={[
          {
            path: '/:toolName',
            onEnter: (params = {}) => {
              if (-1 !== props.tools.findIndex(tool => tool.name === params.toolName)) {
                // tool is enabled for the desktop
                props.openTool(params.toolName, props.workspace)
              } else {
                // tool is disabled (or does not exist) for the desktop
                // let's go to the default opening of the desktop
                props.history.replace('/desktop/workspaces/' + props.workspace.id)
              }
            },
            component: ToolMain
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
        ]}
      />
    )
  }

  return null
}

WorkspaceMain.propTypes = {
  history: T.shape({
    replace: T.func.isRequired
  }).isRequired,
  loaded: T.bool.isRequired,
  managed: T.bool.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  openTool: T.func.isRequired,
  accessErrors: T.object,
  dismissRestrictions: T.func.isRequired
}

WorkspaceMain.defaultProps = {
  tools: []
}

export {
  WorkspaceMain
}
