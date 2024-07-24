import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ContextMenu} from '#/main/app/context/containers/menu'

import {MODAL_PLATFORM_ABOUT} from '#/main/app/contexts/administration/modals/about'

const AdministrationMenu = props =>
  <ContextMenu
    title={trans('administration')}
    tools={props.tools}
    actions={[
      {
        name: 'about',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-circle-info',
        label: trans('show-info', {}, 'actions'),
        modal: [MODAL_PLATFORM_ABOUT]
      }
    ]}
  />

AdministrationMenu.propTypes = {
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

export {
  AdministrationMenu
}
