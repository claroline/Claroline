import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {WorkspaceToolbar} from '#/main/app/layout/sections/workspace/components/toolbar'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {ToolMain} from '#/main/core/tool/components/main'

// TODO : manage empty default opening

const WorkspaceMain = (props) =>
  <Fragment>
    <WorkspaceToolbar
      openedTool={null}
      workspace={props.workspace}
      tools={props.tools}
    />

    <div className="page-container">
      <Routes
        routes={[
          {
            path: '/:toolName',
            render: (routeProps) => {
              if (-1 !== props.tools.findIndex(tool => tool.name === routeProps.match.params.toolName)) {
                // tool is enabled for the workspace
                props.openTool(routeProps.match.params.toolName, props.workspace)

                const WorkspaceTool = (
                  <ToolMain
                    basePath={`/${routeProps.match.params.toolName}`}
                    toolName={routeProps.match.params.toolName}
                  />
                )

                return WorkspaceTool
              }

              // tool is disabled for the workspace
              // let's go to the default opening of the WS
              props.history.push('/')

              return null
            }
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: `/${props.defaultOpening}`}
        ]}
      />
    </div>
  </Fragment>

WorkspaceMain.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,

  defaultOpening: T.string,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  tools: T.arrayOf(T.shape({

  })),
  openTool: T.func.isRequired
}

WorkspaceMain.defaultProps = {
  tools: []
}

export {
  WorkspaceMain
}
