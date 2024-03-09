import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const TrainingsMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'catalog',
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: `${props.path}/catalog`
      }, {
        name: 'public',
        type: LINK_BUTTON,
        label: trans('public_events', {}, 'cursus'),
        target: props.path + '/events/public'
      }, {
        name: 'all',
        type: LINK_BUTTON,
        label: trans('all_events', {}, 'cursus'),
        target: props.path + '/events/all',
        displayed: props.authenticated && (props.canEdit || props.canRegister)
      }, {
        name: 'registered',
        type: LINK_BUTTON,
        label: trans('my_courses', {}, 'cursus'),
        target: `${props.path}/registered`,
        displayed: props.authenticated
      }, {
        name: 'registered-events',
        type: LINK_BUTTON,
        label: trans('my_events', {}, 'cursus'),
        target: props.path + '/events/registered',
        displayed: props.authenticated
      }
    ]}
  />

TrainingsMenu.propTypes = {
  path: T.string,
  canEdit: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  authenticated: T.bool.isRequired
}

export {
  TrainingsMenu
}
