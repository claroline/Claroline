import React, {Component} from 'react'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {ColorChartLibrary} from '#/main/theme/color/components/library'
import {ColorChartSelector} from '#/main/theme/color/components/selector'
import {ColorChart as ColorChartTypes} from '#/main/theme/color/prop-types'

class ColorChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      view: 'selector'
    }

    this.onInputChange = this.onInputChange.bind(this)
    this.onInputBlur = this.onInputBlur.bind(this)
    this.toggleView = this.toggleView.bind(this)
  }

  componentDidMount() {
    if (!this.props.noLibrary) {
      const hasColorChart = this.props.colorChart && this.props.colorChart && this.props.colorChart.length > 0
      this.setState({
        view: this.props.view ? this.props.view : hasColorChart ? 'library' : 'selector'
      })
    }
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

  toggleView() {
    if ('library' === this.state.view) {
      this.setState({view: 'selector'})
    } else {
      this.setState({view: 'library'})
    }
  }

  renderView() {
    switch (this.state.view) {
      case 'library':
        return (
          <ColorChartLibrary
            colorCharts={this.props.colorChart}
            selected={this.props.selected}
            onChange={this.props.onChange}
          />
        )
      case 'selector':
        return (
          <ColorChartSelector
            selected={this.props.selected}
            onChange={this.props.onChange}
          />
        )
    }
  }

  render() {
    let color
    if (this.props.selected) {
      color = tinycolor(this.props.selected)
    }

    const noLibrary = this.props.noLibrary || (this.props.colorChart && this.props.colorChart.length === 0)

    return (
      <div className="color-chart-container">
        {this.props.showCurrent &&
          <div
            className={classes('current-container', {
              'text-light': color && color.isDark(),
              'text-dark': color && color.isLight(),
              'transparent': !color || 1 > color.getAlpha()
            })}
            style={{
              backgroundColor: this.props.selected
            }}
          >
            <input
              type="text"
              className={classes('current-color', {
                'text-light': color && color.isDark(),
                'text-dark': color && color.isLight()
              })}
              value={this.props.selected || ''}
              placeholder="#FFFFFF"
              onChange={this.onInputChange}
              onBlur={this.onInputBlur}
            />
            {!noLibrary &&
              <CallbackButton
                className={classes('btn-link btn-view w-100', {
                  'text-light': color && color.isDark(),
                  'text-dark': color && color.isLight()
                })}
                callback={this.toggleView}
                size="sm"
              >
                {trans('library' === this.state.view ? 'colors-selector':'colors-library')}
              </CallbackButton>
            }
          </div>
        }

        <div className="colors-container">
          {this.renderView()}
        </div>
      </div>
    )
  }
}

implementPropTypes(ColorChart, ColorChartTypes)

export {
  ColorChart
}
