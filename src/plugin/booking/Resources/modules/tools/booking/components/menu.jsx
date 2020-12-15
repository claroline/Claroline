import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const BookingMenu = props =>
  <MenuSection
    {...omit(props)}
    className="agenda-menu"
    title={trans('booking', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'materials',
          type: LINK_BUTTON,
          label: trans('materials', {}, 'booking'),
          target: props.path + '/materials'
        }, {
          name: 'rooms',
          type: LINK_BUTTON,
          label: trans('rooms', {}, 'booking'),
          target: props.path + '/rooms'
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

BookingMenu.propTypes = {
  path: T.string.isRequired,
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  BookingMenu
}
