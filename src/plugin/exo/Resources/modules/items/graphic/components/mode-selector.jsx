import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {MODE_RECT, MODE_CIRCLE, MODE_SELECT} from '#/plugin/exo/items/graphic/constants'

const ModeSelector = props =>
  <Toolbar
    className="btn-group"
    buttonName="btn"
    tooltip="bottom"
    actions={[
      {
        name: 'select',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-hand-pointer',
        label: trans('graphic_mode_select', {}, 'quiz'),
        active: props.currentMode === MODE_SELECT,
        callback: () => props.onChange(MODE_SELECT)
      }, {
        name: 'rect',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-square',
        label: trans('graphic_mode_rect', {}, 'quiz'),
        active: props.currentMode === MODE_RECT,
        callback: () => props.onChange(MODE_RECT)
      }, {
        name: 'circle',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-circle',
        label: trans('graphic_mode_circle', {}, 'quiz'),
        active: props.currentMode === MODE_CIRCLE,
        callback: () => props.onChange(MODE_CIRCLE)
      }
    ]}
  />

ModeSelector.propTypes = {
  currentMode: T.oneOf([MODE_RECT, MODE_CIRCLE, MODE_SELECT]),
  onChange: T.func.isRequired
}

export {
  ModeSelector
}
