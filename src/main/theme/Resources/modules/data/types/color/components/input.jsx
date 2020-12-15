import React, {Component} from 'react'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {ColorChart} from '#/main/theme/color/components/color-chart'

class ColorInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
    this.onInputBlur = this.onInputBlur.bind(this)
  }

  onInputChange(e) {
    this.props.onChange(e.target.value)
  }

  onInputBlur(e) {
    // format color string
    const color = tinycolor(e.target.value)
    if (color.isValid()) {
      if (1 > color.getAlpha()) {
        // convert to rgba
        this.props.onChange(color.toRgbString())
      } else {
        // no alpha => convert to hex
        this.props.onChange(color.toHexString())
      }
    }
  }

  renderPickerButton(className) {
    let color
    if (this.props.value) {
      color = tinycolor(this.props.value)
    }

    return (
      <Button
        className={classes('btn', className, {
          'text-light': color && color.isDark(),
          'text-dark': color && color.isLight()
        })}
        style={{
          background: this.props.value,
          borderColor: this.props.value
        }}
        type={MENU_BUTTON}
        icon={this.props.colorIcon}
        label={trans('show-colors', {}, 'actions')}
        tooltip="right"
        size={this.props.size}
        disabled={this.props.disabled}
        menu={
          <div className="dropdown-menu">
            <ColorChart
              selected={this.props.value}
              onChange={this.props.onChange}
            />
          </div>
        }
      />
    )
  }

  render() {
    if (this.props.hideInput) {
      return this.renderPickerButton(this.props.className)
    }

    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <span className="input-group-btn">
          {this.renderPickerButton()}
        </span>

        <input
          id={this.props.id}
          type="text"
          autoComplete={this.props.autoComplete}
          className="form-control"
          placeholder={this.props.placeholder || '#FFFFFF'}
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={this.onInputChange}
          onBlur={this.onInputBlur}
        />
      </div>
    )
  }
}

implementPropTypes(ColorInput, DataInputTypes, {
  // more precise value type
  value: T.string,

  // custom options
  hideInput: T.bool,
  colorIcon: T.string,
  colors: T.arrayOf(T.string)
}, {
  hideInput: false,
  colorIcon: 'fa fa-fw fa-palette',
  colors: [
    '#FF6900',
    '#FCB900',
    '#7BDCB5',
    '#00D084',
    '#8ED1FC',
    '#0693E3',
    '#ABB8C3',
    '#EB144C',
    '#FFFFFF',
    '#000000'
  ]
})

export {
  ColorInput
}
