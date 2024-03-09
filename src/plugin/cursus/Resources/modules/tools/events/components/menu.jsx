import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const EventsMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'registered',
        type: LINK_BUTTON,
        label: trans('my_events', {}, 'cursus'),
        target: props.path + '/registered'
      }, {
        name: 'public',
        type: LINK_BUTTON,
        label: trans('public_events', {}, 'cursus'),
        target: props.path + '/public'
      }, {
        name: 'all',
        type: LINK_BUTTON,
        label: trans('all_events', {}, 'cursus'),
        target: props.path + '/all',
        displayed: props.canEdit || props.canRegister
      }, {
        name: 'presences',
        type: LINK_BUTTON,
        label: trans('presences', {}, 'cursus'),
        target: props.path + '/presences',
        displayed: props.canEdit || props.canRegister
      }
    ]}
  />

EventsMenu.propTypes = {
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired
}

export {
  EventsMenu
}
