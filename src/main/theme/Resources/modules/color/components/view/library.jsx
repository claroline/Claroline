import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const colors = [
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

class ColorChartLibrary extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentLibrary: 'default'
    }
  }

  render() {
    const selectedObject = tinycolor(this.props.selected)

    return (
      <div className="color-chart-library">
        {colors.map(color => {
          const colorObject = tinycolor(color)

          return (
            <CallbackButton
              key={color}
              className="color"
              style={{
                background: color
              }}
              callback={() => this.props.onChange(color)}
            >
              {(this.props.selected && selectedObject.toRgbString() === colorObject.toRgbString()) &&
                <span className={classes('fa fa-check', {
                  'text-light': colorObject.isDark(),
                  'text-dark': colorObject.isLight()
                })} />
              }

              <span className="sr-only">{color}</span>
            </CallbackButton>
          )
        })}
      </div>
    )
  }
}

ColorChartLibrary.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired
}

export {
  ColorChartLibrary
}
