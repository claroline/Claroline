import React from 'react'

import {Await} from '#/main/app/components/await'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ToolPage} from '#/main/core/tool/containers/page'
import {getApps} from '#/main/app/plugins'

function getIntegrationApps() {
  const apps = getApps('integration', false)

  return Promise.all(Object.keys(apps).map(type => apps[type]()))
}

export const IntegrationTool = () =>
  <ToolPage>
    <Await
      for={getIntegrationApps()}
      then={(apps) => {

        const tabs = []
        const routes = []

        apps.map(app => {
          tabs.push({
            icon: app.default.icon,
            title: trans(app.default.name, {}, 'tools'),
            path: '/'+app.default.name
          })

          routes.push({
            path: '/'+app.default.name,
            component: app.default.component
          })
        })

        return (
          <div className="row">
            <div className="col-md-3">
              <Vertical
                style={{
                  marginTop: '20px' // FIXME
                }}
                tabs={tabs}
              />
            </div>

            <div className="col-md-9">
              <Routes
                routes={routes}
              />
            </div>
          </div>
        )}}
    />
  </ToolPage>
