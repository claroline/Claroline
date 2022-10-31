import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isFinite from 'lodash/isFinite'

import {interpolateRound} from 'd3-interpolate'
import {scaleLinear} from 'd3-scale'
import {select} from 'd3-selection'
import {arc} from 'd3-shape'
import 'd3-transition'

import {constants} from '#/main/core/layout/gauge/constants'

/**
 * Renders the Gauge SVG area.
 */
const GaugeContainer = props =>
  <svg
    width={props.width}
    height={props.height}
    className={classes('gauge', `gauge-${props.type}`, props.className)}
  >
    <g transform={`translate(${props.width/2 - props.radius}, ${props.height/2 - props.radius})`}>
      {props.children}
    </g>
  </svg>

GaugeContainer.propTypes = {
  className: T.string,
  type: T.oneOf(['primary', 'success', 'warning', 'danger', 'info', 'user', 'custom']).isRequired,
  width: T.oneOfType([T.string, T.number]).isRequired,
  height: T.oneOfType([T.string, T.number]).isRequired,
  radius: T.number.isRequired,
  children: T.node
}

/**
 * Renders the Gauge outer circle.
 *
 * @todo add animation
 */
class GaugeBorder extends Component {
  componentDidMount() {
    // Make the value count up.
    if (!this.props.preFilled) {
      this.updateFilling(0, this.props.filling)
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.props.preFilled && prevProps.filling !== this.props.filling) {
      this.updateFilling(prevProps.filling || 0, this.props.filling)
    }
  }

  updateFilling(currentFilling, newFilling) {
    const circleX = scaleLinear().range([0, 2*Math.PI]).domain([0, 100])
    const circleY = scaleLinear().range([0, this.props.radius]).domain([0, this.props.radius])

    const outerRadius = circleY(this.props.radius)
    const innerRadius = circleY(this.props.radius - this.props.thickness)

    const i = interpolateRound(currentFilling, newFilling)

    // append transition
    const filled = select(this.filled)
    filled
      .transition()
      .duration(constants.GAUGE_FILLING_DURATION)
      .attrTween('d', () => {
        return (t) => {
          return arc()
            .startAngle(circleX(0))
            .endAngle(circleX(i(t)))
            .outerRadius(outerRadius)
            .innerRadius(innerRadius)()
        }
      })

    const remaining = select(this.remaining)
    remaining
      .transition()
      .duration(constants.GAUGE_FILLING_DURATION)
      .attrTween('d', () => {
        return (t) => {
          return arc()
            .startAngle(circleX(i(t)))
            .endAngle(circleX(100))
            .outerRadius(outerRadius)
            .innerRadius(innerRadius)()
        }
      })
  }

  render() {
    const circleX = scaleLinear().range([0, 2*Math.PI]).domain([0, 100])
    const circleY = scaleLinear().range([0, this.props.radius]).domain([0, this.props.radius])

    const fillingStartValue = this.props.preFilled ? this.props.filling : 0

    const filledArc = arc()
      .startAngle(circleX(0))
      .endAngle(circleX(fillingStartValue))
      .outerRadius(circleY(this.props.radius))
      .innerRadius(circleY(this.props.radius - this.props.thickness))

    const remainingArc = arc()
      .startAngle(circleX(fillingStartValue))
      .endAngle(circleX(100))
      .outerRadius(circleY(this.props.radius))
      .innerRadius(circleY(this.props.radius - this.props.thickness))

    return (
      <g>
        <path
          ref={el => this.remaining = el}
          className="gauge-border gauge-border-empty"
          d={remainingArc()}
          transform={`translate(${this.props.radius}, ${this.props.radius})`}
        />

        <path
          ref={el => this.filled = el}
          className="gauge-border gauge-border-filled"
          d={filledArc()}
          transform={`translate(${this.props.radius}, ${this.props.radius})`}
          strokeLinecap="round"
          style={this.props.color ? {fill: this.props.color} : undefined}
        />
      </g>
    )
  }
}

GaugeBorder.propTypes = {
  radius: T.number.isRequired,
  thickness: T.number.isRequired,
  filling: T.number,
  preFilled: T.bool,
  color: T.string
}

GaugeBorder.defaultProps = {
  filling: 100
}

/**
 * Renders the gauge texts.
 */
class GaugeText extends Component {
  componentDidMount() {
    // Make the value count up.
    if (!this.props.preFilled && isFinite(this.props.value)) {
      this.updateText(0, this.props.value)
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.props.preFilled && prevProps.value !== this.props.value && isFinite(this.props.value)) {
      this.updateText(prevProps.value || 0, this.props.value)
    }
  }

  updateText(currentValue, newValue) {
    const writeValue = (value) => this.props.displayValue(value)

    const i = interpolateRound(currentValue, newValue)

    // append transition to text nodes
    const text = select(this.text)
    text
      .transition()
      .duration(constants.GAUGE_FILLING_DURATION)
      .tween('text', () => {
        return (t) => {
          if (1 === t) {
            currentValue = newValue
          } else {
            currentValue = i(t)
            if (currentValue > newValue) {
              currentValue = newValue
            }
          }

          text.text(
            writeValue(currentValue)
          )
        }
      })
  }

  render() {
    const textStartValue = !this.props.preFilled && isFinite(this.props.value) ? 0 : this.props.value

    const fillCircleRadius = this.props.radius - this.props.margin
    const textPixels = constants.GAUGE_TEXT_SIZE * (this.props.radius/2)

    // Scale for controlling the position of the text within the gauge.
    const textRiseScaleY = scaleLinear()
      .range([this.props.margin + fillCircleRadius*2, (this.props.margin + textPixels*0.7)])
      .domain([0, 1])

    return (
      <text
        ref={el => this.text = el}
        className={classes('gauge-text', this.props.className)}
        fontSize={textPixels}
        textAnchor="middle"
        transform={`translate(${this.props.radius}, ${textRiseScaleY(this.props.position)})`}
        style={this.props.color ? {fill: this.props.color} : undefined}
      >
        {this.props.displayValue(textStartValue)}
      </text>
    )
  }
}

GaugeText.propTypes = {
  className: T.string,
  value: T.number,
  displayValue: T.func,
  radius: T.number.isRequired,
  margin: T.number.isRequired, // margin generated by border and gap width
  preFilled: T.bool.isRequired,
  position: T.number,
  color: T.string
}

GaugeText.defaultProps = {
  displayValue: (value) => value,
  position: constants.GAUGE_TEXT_POSITION
}

export {
  GaugeContainer,
  GaugeBorder,
  GaugeText
}
