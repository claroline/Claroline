import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {constants as toolConst} from '#/main/core/tool/constants'
import {ToolMain} from '#/main/core/tool/containers/main'

const AdministrationMain = (props) =>
  <Await
    for={props.open(props.loaded)}
    placeholder={
      <ContentLoader
        size="lg"
        description="Nous chargeons l'administration"
      />
    }
    then={() => {
      return (
        <Routes
          path="/admin"
          routes={[
            {
              path: '/:toolName',
              render: (routeProps) => {
                if (-1 !== props.tools.findIndex(tool => tool.name === routeProps.match.params.toolName)) {
                  // tool is enabled for the admin
                  const AdministrationTool = (
                    <ToolMain
                      path="/admin"
                      toolName={routeProps.match.params.toolName}
                      toolContext={{
                        type: toolConst.TOOL_ADMINISTRATION,
                        url: ['claro_admin_open_tool', {toolName: routeProps.match.params.toolName}],
                        data: {}
                      }}
                    />
                  )

                  return AdministrationTool
                }

                // tool is disabled (or does not exist) for the admin
                // let's go to the default opening of the admin
                routeProps.history.push('/admin')

                return null
              }
            }
          ]}
          redirect={[
            {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
          ]}
        />
      )
    }}
  />

AdministrationMain.propTypes = {
  loaded: T.bool.isRequired,
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  open: T.func.isRequired
}

AdministrationMain.defaultProps = {
  tools: []
}

export {
  AdministrationMain
}
