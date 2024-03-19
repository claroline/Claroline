import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ToolMenu} from '#/main/core/tool/containers/menu'

const ResourcesMenu = (props) =>
  <ToolMenu
    actions={[
      {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-home',
        label: trans('trash'),
        target: `${props.path}/trash`,
        displayed: props.canAdministrate
      }, {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-sitemap',
        label: trans('Arborescence'),
        target: `${props.path}/summary`
      }
    ]}
  />

ResourcesMenu.propTypes = {
  path: T.string.isRequired,
  canAdministrate: T.bool.isRequired
}

export {
  ResourcesMenu
}
