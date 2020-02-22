import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import tinycolor from 'tinycolor2'

class ColorSelector extends Component {
  constructor(props){
    super(props)

    this.onClick = this.onClick.bind(this)
  }

  onClick(e) {
    const rect = e.target.getBoundingClientRect()
    const x = e.clientX - rect.left // x position within the element.
    const y = rect.height - (e.clientY - rect.top) // y position within the element.

    const saturation = Math.floor((x / rect.width) * 100)
    const value = Math.floor((y / rect.height) * 100)

    const color = tinycolor(this.props.selected).toHsv()
    const updated = tinycolor({h: color.h, s: saturation, v: value, a: color.a})

    this.props.onChange(updated.toRgbString())
  }

  render() {
    const original = tinycolor(this.props.selected).toHsv()

    const rawColor = tinycolor({ h: original.h, s: 100, v: 100 })

    return (
      <div
        className="color"
        onClick={this.onClick}
        style={{
          background: rawColor.toRgbString()
        }}
      >
        <div className="color-gradient"/>

        <button
          type="button"
          className="color-cursor"
          style={{
            left: `${original.s * 100}%`,
            bottom: `${original.v * 100}%`
          }}
        >
          <span className="sr-only">change value</span>
        </button>
      </div>
    )
  }
}

ColorSelector.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired
}

class HueSelector extends Component {
  constructor(props) {
    super(props)

    this.onSliderClick = this.onSliderClick.bind(this)
  }

  onSliderClick(e) {
    const rect = e.target.getBoundingClientRect()
    const y = rect.height - (e.clientY - rect.top) // y position within the element.

    const hueValue = Math.floor((y / rect.height) * 360)

    const color = tinycolor(this.props.selected).toHsv()
    const updated = tinycolor({h: hueValue, s: color.s, v: color.v, a: color.a})

    this.props.onChange(updated.toRgbString())
  }

  render() {
    const original = tinycolor(this.props.selected).toHsv()

    return (
      <div
        className="color-slider color-slider-v color-hue"
        onClick={this.onSliderClick}
      >
        <button
          type="button"
          className="color-slider-cursor"
          style={{
            bottom: `${(original.h / 360)*100}%`
          }}
        >
          <span className="sr-only">change value</span>
        </button>
      </div>
    )
  }
}

class AlphaSelector extends Component {
  constructor(props) {
    super(props)

    this.onSliderClick = this.onSliderClick.bind(this)
  }

  onSliderClick(e) {
    const rect = e.target.getBoundingClientRect()
    const x = e.clientX - rect.left // x position within the element.

    const alphaValue = Math.floor((x / rect.width) * 100)

    const color = tinycolor(this.props.selected)
    color.setAlpha(alphaValue / 100)

    this.props.onChange(color.toRgbString())
  }

  render() {
    // to get current alpha value
    const original = tinycolor(this.props.selected)

    const currentAlpha = original.getAlpha()

    return (
      <div
        className="color-slider color-slider-h color-alpha"
        onClick={this.onSliderClick}
      >
        <div
          className="color-slider-current"
          style={{
            background: `linear-gradient(to right, ${original.setAlpha(0).toRgbString()}, ${original.setAlpha(1).toRgbString()})`
          }}
        />

        <button
          type="button"
          className="color-slider-cursor"
          style={{
            left: `${currentAlpha * 100}%`
          }}
        >
          <span className="sr-only">change value</span>
        </button>
      </div>
    )
  }
}

AlphaSelector.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired
}

const ColorChartSelector = props =>
  <div className="color-chart-selector">
    <ColorSelector
      selected={props.selected}
      onChange={props.onChange}
    />

    <HueSelector
      selected={props.selected}
      onChange={props.onChange}
    />

    <AlphaSelector
      selected={props.selected}
      onChange={props.onChange}
    />
  </div>

ColorChartSelector.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired
}

export {
  ColorChartSelector
}
