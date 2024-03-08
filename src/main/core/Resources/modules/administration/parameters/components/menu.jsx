import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ToolMenu} from '#/main/core/tool/containers/menu'

const ParametersMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'general',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-circle-info',
        label: trans('general'),
        target: props.path+'/',
        exact: true
      }, {
        name: 'appearance',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-drafting-compass',
        label: trans('appearance'),
        target: props.path+'/appearance'
      }/*, {
        name: 'technical',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-wrench',
        label: trans('technical'),
        target: props.path+'/technical'
      }*/
    ]}
  />

ParametersMenu.propTypes = {
  path: T.string.isRequired
}

export {
  ParametersMenu
}
