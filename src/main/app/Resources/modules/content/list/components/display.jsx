import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {DataListDisplay} from '#/main/app/content/list/prop-types'
import DISPLAY_MODES from '#/main/app/content/list/modes'

const ListDisplay = props =>
  <Button
    id="list-format-toggle"
    className={classes('list-header-btn btn btn-text-body px-2', props.className)}
    type={MENU_BUTTON}
    icon={DISPLAY_MODES[props.current].icon}
    label={trans('list_display_modes_title')}
    tooltip="bottom"
    disabled={props.disabled}
    menu={{
      align: 'right',
      label: trans('list_display_modes'),
      items: props.available.map(display => ({
        type: CALLBACK_BUTTON,
        icon: DISPLAY_MODES[display].icon,
        label: DISPLAY_MODES[display].label,
        active: display === props.current,
        callback: () => props.changeDisplay(display)
      }))
    }}
  />

implementPropTypes(ListDisplay, DataListDisplay, {
  disabled: T.bool,
  className: T.string
})

export {
  ListDisplay
}
