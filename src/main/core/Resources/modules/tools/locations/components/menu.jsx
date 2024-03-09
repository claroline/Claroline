import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const LocationsMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'locations',
        type: LINK_BUTTON,
        label: trans('locations'),
        target: `${props.path}/locations`
      }, {
        name: 'materials',
        type: LINK_BUTTON,
        label: trans('materials', {}, 'location'),
        target: `${props.path}/materials`
      }, {
        name: 'rooms',
        type: LINK_BUTTON,
        label: trans('rooms', {}, 'location'),
        target: `${props.path}/rooms`
      }
    ]}
  />

LocationsMenu.propTypes = {
  path: T.string.isRequired
}

export {
  LocationsMenu
}
