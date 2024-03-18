import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ToolMenu} from '#/main/core/tool/containers/menu'

const AppearanceMenu = (props) =>
  <ToolMenu
    actions={[
      /*{
        name: 'appearance',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-drafting-compass',
        label: trans('appearance'),
        target: props.path+'/'
      }*/
    ]}
  />

AppearanceMenu.propTypes = {
  path: T.string.isRequired
}

export {
  AppearanceMenu
}
