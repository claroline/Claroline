import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {getIntegrations} from '#/main/core/integration'

const IntegrationMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('integration', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={getIntegrations().then((apps) => apps.map(app => ({
        name: app.default.name,
        type: LINK_BUTTON,
        icon: app.default.icon,
        label: trans(app.default.name, {}, 'integration'),
        target: `${props.path}/${app.default.name}`
      })))}
      onClick={props.autoClose}
    />
  </MenuSection>

IntegrationMenu.propTypes = {
  path: T.string,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  IntegrationMenu
}
