import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {getApps} from '#/main/app/plugins'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Await} from '#/main/app/components/await'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

function getIntegrationApps() {
  const apps = getApps('integration')

  return Promise.all(Object.keys(apps).map(type => apps[type]()))
}

const IntegrationMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('integration', {}, 'tools')}
  >
    <Await
      for={getIntegrationApps()}
      then={(apps) => {

        const actions = []

        apps.map(app => {
          actions.push({
            name: app.default.name,
            type: LINK_BUTTON,
            icon: app.default.icon,
            label: trans(app.default.name, {}, 'tools'),
            target: `${props.path}/${app.default.name}`
          })
        })

        return (
          <Toolbar
            className="list-group"
            buttonName="list-group-item"
            actions={actions}
          />
        )}}
    />
  </MenuSection>

IntegrationMenu.propTypes = {
  path: T.string
}

export {
  IntegrationMenu
}
