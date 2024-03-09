import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {getIntegrations} from '#/main/core/integration'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const IntegrationMenu = (props) =>
  <ToolMenu
    actions={getIntegrations().then((apps) => apps.map(app => ({
      name: app.default.name,
      type: LINK_BUTTON,
      icon: app.default.icon,
      label: trans(app.default.name, {}, 'integration'),
      target: `${props.path}/${app.default.name}`
    })))}
  />

IntegrationMenu.propTypes = {
  path: T.string
}

export {
  IntegrationMenu
}
