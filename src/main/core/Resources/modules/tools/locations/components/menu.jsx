import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const LocationsMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('locations', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
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
      onClick={props.autoClose}
    />
  </MenuSection>

LocationsMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  LocationsMenu
}
