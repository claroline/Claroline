import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const EventsMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'about',
        type: LINK_BUTTON,
        label: trans('about', {}, 'platform'),
        target: props.course ? props.path + '/about/' + props.course.slug : props.path + '/about'
      },{
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
        label: (props.canEdit || props.canRegister) ? trans('presences', {}, 'cursus') : trans('my_presences', {}, 'cursus'),
        target: props.path + '/presences'
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
