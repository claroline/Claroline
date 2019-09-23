import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/app/content/list/constants'

const ListDisplay = props =>
  <Button
    id="list-format-toggle"
    className="list-header-btn btn btn-link"
    type={MENU_BUTTON}
    icon={constants.DISPLAY_MODES[props.current].icon}
    label={trans('list_display_modes_title')}
    tooltip="bottom"
    disabled={props.disabled}
    menu={{
      align: 'right',
      label: trans('list_display_modes'),
      items: props.available.map(display => ({
        type: CALLBACK_BUTTON,
        icon: constants.DISPLAY_MODES[display].icon,
        label: constants.DISPLAY_MODES[display].label,
        active: display === props.current,
        callback: () => props.change(display)
      }))
    }}
  />

ListDisplay.propTypes = {
  disabled: T.bool,
  current: T.oneOf(Object.keys(constants.DISPLAY_MODES)).isRequired,
  available: T.arrayOf(
    T.oneOf(Object.keys(constants.DISPLAY_MODES))
  ).isRequired,
  change: T.func.isRequired
}

export {
  ListDisplay
}
