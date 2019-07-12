import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {constants as toolConst} from '#/main/core/tool/constants'
import {ToolMain} from '#/main/core/tool/containers/main'

const DesktopMain = (props) =>
  <Await
    for={props.open(props.loaded)}
    placeholder={
      <ContentLoader
        size="lg"
        description="Nous chargeons votre bureau"
      />
    }
    then={() => (
      <Routes
        path="/desktop"
        routes={[
          {
            path: '/:toolName',
            render: (routeProps) => {
              if (-1 !== props.tools.findIndex(tool => tool.name === routeProps.match.params.toolName)) {
                // tool is enabled for the desktop
                const DesktopTool = (
                  <ToolMain
                    path="/desktop"
                    toolName={routeProps.match.params.toolName}
                    toolContext={{
                      type: toolConst.TOOL_DESKTOP,
                      url: ['claro_desktop_open_tool', {toolName: routeProps.match.params.toolName}],
                      data: {}
                    }}
                  />
                )

                return DesktopTool
              }

              // tool is disabled (or does not exist) for the desktop
              // let's go to the default opening of the desktop
              routeProps.history.push('/desktop')

              return null
            }
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
        ]}
      />
    )}
  />

DesktopMain.propTypes = {
  loaded: T.bool.isRequired,
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  open: T.func.isRequired
}

DesktopMain.defaultProps = {
  tools: []
}

export {
  DesktopMain
}
