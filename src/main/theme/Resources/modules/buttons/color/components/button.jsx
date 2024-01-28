import React, {forwardRef} from 'react'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

import {ColorChart} from '#/main/theme/color/containers/color-chart'
import {Menu} from '#/main/app/overlays/menu'

const ColorMenu = forwardRef((props, ref) =>
  <div {...omit(props, 'value', 'onChange', 'show', 'close')} ref={ref}>
    <ColorChart
      selected={props.value}
      onChange={props.onChange}
    />
  </div>
)

ColorMenu.propTypes = {
  value: T.string,
  onChange: T.func.isRequired
}

const ColorButton = forwardRef((props, ref) => {
  let color
  if (props.color) {
    color = tinycolor(props.color)
  }

  return (
    <MenuButton
      {...omit(props, 'color')}
      className={classes('btn', props.className, {
        'text-light': color && color.isDark(),
        'text-dark': color && color.isLight()
      })}
      style={{
        background: props.color,
        borderColor: props.color
      }}
      menu={
        <Menu
          as={ColorMenu}
          value={props.color}
          onChange={props.onSelect}
        />
      }
      ref={ref}
    />
  )
})

implementPropTypes(ColorButton, ButtonTypes, {
  color: T.string,
  onSelect: T.func.isRequired
})

export {
  ColorButton
}
