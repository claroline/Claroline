import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {getApps} from '#/main/app/plugins'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'

import {ToolPage} from '#/main/core/tool/containers/page'

function getIntegrationApps() {
  const apps = getApps('integration', false)

  return Promise.all(Object.keys(apps).map(type => apps[type]()))
}

const IntegrationTool = props =>
  <Await
    for={getIntegrationApps()}
    then={(apps) => {
      const routes = []
      const subtitlesRoutes = []

      apps.map(app => {
        routes.push({
          path: `/${app.default.name}`,
          component: app.default.component
        })
        subtitlesRoutes.push({
          path: `/${app.default.name}`,
          render: () => trans(app.default.name, {}, 'tools')
        })
      })

      return (
        <ToolPage
          subtitle={
            <Routes
              path={props.path}
              routes={subtitlesRoutes}
            />
          }
        >
          <Routes
            path={props.path}
            routes={routes}
          />
        </ToolPage>
      )
    }}
  />

IntegrationTool.propTypes = {
  path: T.string.isRequired
}

export {
  IntegrationTool
}