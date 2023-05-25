import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import {Select} from '#/main/app/input/components/select'
import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

class ColorChartLibrary extends Component {
  constructor(props) {
    super(props)

    this.state = {
      colorCharts: typeof props.colorCharts === 'undefined' ? [] : props.colorCharts,
      selectedColorChart: 'all'
    }

    this.handleColorChartSelect = this.handleColorChartSelect.bind(this)
  }

  componentDidUpdate(prevProps)  {
    if(this.props.colorCharts !== prevProps.colorCharts) {
      this.setState({
        colorCharts: typeof this.props.colorCharts === 'undefined' ? []   : this.props.colorCharts,
        selectedColorChart: 'all'
      })
    }
  }

  handleColorChartSelect(selectedOption) {
    this.setState({selectedColorChart: selectedOption})
  }

  render() {
    const selectedObject = tinycolor(this.props.selected)
    return (
      <div className="select-color-chart">
        <Select
          id="color-chart-select"
          size="md"
          value={this.state.selectedColorChart}
          onChange={this.handleColorChartSelect}
          choices={this.state.colorCharts.reduce((choices, colorChart) => {
            return {...choices, [colorChart.name]: colorChart.name}
          }, {
            'all': trans('all')
          })}
        />

        {this.state.colorCharts.map(colorChart => {
          const colorChartDots = colorChart.colors.map(color => {
            if (colorChart.name === this.state.selectedColorChart || this.state.selectedColorChart === 'all') {
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
                    <span
                      className={classes('fa fa-check', {
                        'text-light': colorObject.isDark(),
                        'text-dark': colorObject.isLight()
                      })}/>}
                  <span className="sr-only">{color}</span>
                </CallbackButton>)
            }
          })
          return (
            <span className="color-chart-library">
              {colorChartDots}
            </span>
          )
        })}
      </div>
    )
  }
}

ColorChartLibrary.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired,
  colorCharts: T.array.isRequired,
}

export {
  ColorChartLibrary
}
