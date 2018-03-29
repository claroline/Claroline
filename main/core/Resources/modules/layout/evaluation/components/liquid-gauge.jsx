import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {easeLinear} from 'd3-ease'
import {interpolate} from 'd3-interpolate'
import {scaleLinear} from 'd3-scale'
import {select} from 'd3-selection'
import {arc, area} from 'd3-shape'
import 'd3-transition'

// largely inspired from http://bl.ocks.org/brattonc/5e5ce9beee483220e2f6

const LIQUID_GAUGE_CONFIG = {
  minValue: 0,             // The gauge minimum value.
  maxValue: 100,           // The gauge maximum value.
  circleThickness: 0.08,   // The outer circle thickness as a percentage of it's radius.
  circleFillGap: 0.06,     // The size of the gap between the outer circle and wave circle as a percentage of the outer circles radius.
  waveHeight: 0.1,        // The wave height as a percentage of the radius of the wave circle.
  waveCount: 0.85,         // The number of full waves per width of the wave circle.
  waveRiseTime: 2000,      // The amount of time in milliseconds for the wave to rise from 0 to it's final height.
  waveAnimateTime: 2600,   // The amount of time in milliseconds for a full wave to enter the wave circle.
  waveHeightScaling: true, // Controls wave size scaling at low and high fill percentages. When true, wave height reaches it's maximum at 50% fill, and minimum at 0% and 100% fill. This helps to prevent the wave from making the wave circle from appear totally full or empty when near it's minimum or maximum fill.
  waveOffset: 0,           // The amount to initially offset the wave. 0 = no offset. 1 = offset of one full wave.
  textVertPosition: .5,    // The height at which to display the percentage text withing the wave circle. 0 = bottom, 1 = top.
  textSize: 1,             // The relative height of the text to display in the wave circle. 1 = 50%
  displayPercent: true     // If true, a % symbol is displayed after the value.
}

/**
 * Renders the Gauge SVG area.
 */
const GaugeContainer = props =>
  <svg
    width={props.width}
    height={props.height}
    className={classes('gauge liquid-gauge', `liquid-gauge-${props.type}`, props.className)}
  >
    <g transform={`translate(${props.width/2 - props.radius}, ${props.height/2 - props.radius})`}>
      {props.children}
    </g>
  </svg>

GaugeContainer.propTypes = {
  className: T.string,
  type: T.oneOf(['primary', 'success', 'warning', 'danger', 'info', 'user']).isRequired,
  width: T.oneOfType([T.string, T.number]).isRequired,
  height: T.oneOfType([T.string, T.number]).isRequired,
  radius: T.number.isRequired,
  children: T.node
}

/**
 * Renders the Gauge outer circle.
 */
const GaugeBorder = props => {
  const circleX = scaleLinear().range([0, 2*Math.PI]).domain([0, 1])
  const circleY = scaleLinear().range([0, props.radius]).domain([0, props.radius])

  const circleArc = arc()
    .startAngle(circleX(0))
    .endAngle(circleX(1))
    .outerRadius(circleY(props.radius))
    .innerRadius(circleY(props.radius - props.thickness))

  return (
    <path
      className="gauge-border"
      d={circleArc()}
      transform={`translate(${props.radius}, ${props.radius})`}
    />
  )
}

GaugeBorder.propTypes = {
  radius: T.number.isRequired,
  thickness: T.number.isRequired
}

/**
 * Renders the gauge texts.
 */
class GaugeText extends Component {
  componentDidMount() {
    // Make the value count up.
    if (!this.props.preFilled) {
      this.updateText(this.props.value)
    }
  }

  componentWillReceiveProps(nextProps) {
    if (!this.props.preFilled && nextProps.value !== this.props.value) {
      this.updateText(nextProps.value)
    }
  }

  updateText(newValue) {
    const percentText = LIQUID_GAUGE_CONFIG.displayPercent ? '%' : ''

    // append transition to text nodes
    const text = select(this.text)
    text
      .transition()
      .duration(LIQUID_GAUGE_CONFIG.waveRiseTime)
      .tween('text', () => {
        const currentValue = text.text().replace('%', '')
        const i = interpolate(currentValue, Math.round(newValue))

        return function (t) {
          const newValue = Math.round(i(t)) + percentText

          text.text(newValue)
        }
      })
  }

  render() {
    const textFinalValue = parseFloat(this.props.value).toFixed(0)
    const textStartValue = !this.props.preFilled ? LIQUID_GAUGE_CONFIG.minValue : textFinalValue

    const fillCircleRadius = this.props.radius - this.props.margin
    const textPixels = LIQUID_GAUGE_CONFIG.textSize * this.props.radius/2

    // Scale for controlling the position of the text within the gauge.
    const textRiseScaleY = scaleLinear()
      .range([this.props.margin+fillCircleRadius*2, (this.props.margin+textPixels*0.7)])
      .domain([0, 1])

    return (
      <text
        ref={el => this.text = el}
        className={this.props.className}
        fontSize={textPixels}
        textAnchor="middle"
        transform={`translate(${this.props.radius}, ${textRiseScaleY(LIQUID_GAUGE_CONFIG.textVertPosition)})`}
      >
        {textStartValue + (LIQUID_GAUGE_CONFIG.displayPercent ? '%':'')}
      </text>
    )
  }
}

GaugeText.propTypes = {
  className: T.string,
  value: T.number.isRequired,
  radius: T.number.isRequired,
  margin: T.number.isRequired, // margin generated by border and gap width
  preFilled: T.bool.isRequired
}

/**
 * Renders and animates the gauge liquid.
 */
class GaugeLiquid extends Component {
  constructor(props) {
    super(props)

    // pre calculate things that don't change at runtime (aka not linked to current value)
    const waveLength = (this.props.radius - this.props.margin) * 2 / LIQUID_GAUGE_CONFIG.waveCount
    this.waveClipCount = 1 + LIQUID_GAUGE_CONFIG.waveCount
    this.waveClipWidth = waveLength * this.waveClipCount

    this.waveGroupXPosition = this.props.margin + (this.props.radius - this.props.margin) * 2 - this.waveClipWidth

    // Controls wave size scaling at low and high fill percentages.
    // Wave height reaches it's maximum at 50% fill, and minimum at 0% and 100% fill.
    // This helps to prevent the wave from making the wave circle from appear totally full or empty when near it's minimum or maximum fill.
    this.waveHeightScale = scaleLinear()
      .range([0, LIQUID_GAUGE_CONFIG.waveHeight, 0])
      .domain([0, 50, 100])

    if (this.props.wave) {
      // generate wave animation data
      this.waveAnimation = scaleLinear()
      // Push the clip area one full wave then snap back.
        .range([0, this.waveClipWidth - (this.props.radius - this.props.margin) * 2])
        .domain([0, 1])
    }

    this.animate = this.animate.bind(this)
    this.fill = this.fill.bind(this)
  }

  componentDidMount() {
    // avoid reselect nodes at each animation update
    this.waveNode = select(this.wave)

    this.fill(0, this.props.fillPercent)

    if (this.props.wave) {
      this.animate()
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.fillPercent !== this.props.fillPercent) {
      this.fill(this.props.fillPercent, nextProps.fillPercent)
    }
  }

  fill(oldValue, newValue) {
    // Recalculate current waves height
    const waveHeight = (this.props.radius - this.props.margin) * this.waveHeightScale(newValue*100)

    // Scales for controlling the position of the clipping path.
    const waveRiseScale = scaleLinear()
      // The clipping area size is the height of the fill circle + the wave height, so we position the clip wave
      // such that the it will overlap the fill circle at all when at 0%, and will totally cover the fill
      // circle at 100%.
      .range([(this.props.margin + (this.props.radius - this.props.margin) * 2 + waveHeight), this.props.margin - waveHeight])
      .domain([0, 1])

    const wave = select(this.wave)
    const waveDef = select(this.waveDef)
    if (!this.props.preFilled){
      waveDef
        .attr('transform', `translate(${this.waveGroupXPosition}, ${waveRiseScale(oldValue)})`)
        .transition()
        .duration(LIQUID_GAUGE_CONFIG.waveRiseTime)
        .attr('transform', `translate(${this.waveGroupXPosition}, ${waveRiseScale(newValue)})`)
        // This transform is necessary to get the clip wave positioned correctly when waveRise=true and waveAnimate=false.
        // The wave will not position correctly without this, but it's not clear why this is actually necessary.
        .on('start', () => wave.attr('transform', 'translate(1, 0)'))
    } else {
      waveDef
        .attr('transform', `translate(${this.waveGroupXPosition}, ${waveRiseScale(newValue)})`)
    }
  }

  animate() {
    this.waveNode
      .attr('transform', `translate(${this.waveAnimation(this.waveNode.attr('T'))}, 0)`)
      .transition()
      .duration(LIQUID_GAUGE_CONFIG.waveAnimateTime * (1-this.waveNode.attr('T')))
      .ease(easeLinear)
      .attr('transform', `translate(${this.waveAnimation(1)}, 0)`)
      .attr('T', 1)
      .on('end', () => {
        this.waveNode.attr('T', 0)

        this.animate()
      })
  }

  render() {
    const waveHeight = (this.props.radius - this.props.margin) * this.waveHeightScale(this.props.fillPercent*100)

    // Scales for controlling the size of the clipping path.
    const waveScaleX = scaleLinear()
      .range([0, this.waveClipWidth])
      .domain([0, 1])
    const waveScaleY = scaleLinear()
      .range([0, waveHeight]).domain([0, 1])

    const data = []
    for (let i = 0; i <= 40 * this.waveClipCount; i++) {
      data.push({
        x: i / (40 * this.waveClipCount),
        y: i / 40
      })
    }

    const clipArea = area()
      .x(d => waveScaleX(d.x))
      .y0(d => waveScaleY(Math.sin(Math.PI*2*LIQUID_GAUGE_CONFIG.waveOffset*-1 + Math.PI*2*(1-LIQUID_GAUGE_CONFIG.waveCount) + d.y*2*Math.PI)))
      .y1(() => (this.props.radius - this.props.margin)*2 + waveHeight)

    return (
      <g clipPath={`url(#clipWave${this.props.id})`}>
        <defs>
          <clipPath
            ref={el => this.waveDef = el}
            id={`clipWave${this.props.id}`}
          >
            <path
              ref={el => this.wave = el}
              d={clipArea(data)}
              T={0}
            />
          </clipPath>
        </defs>

        <circle
          className="gauge-liquid"
          cx={this.props.radius}
          cy={this.props.radius}
          r={this.props.radius - this.props.margin}
        />

        {this.props.children}
      </g>
    )
  }
}

GaugeLiquid.propTypes = {
  id: T.string.isRequired,
  fillPercent: T.number.isRequired,
  radius: T.number.isRequired,
  margin: T.number.isRequired, // margin generated by border and gap width
  preFilled: T.bool.isRequired,
  wave: T.bool.isRequired,
  children: T.node
}

/**
 * @todo : finish clean code
 * @todo : optimize
 */
const LiquidGauge = props => {
  const radius = Math.min(parseInt(props.width), parseInt(props.height)) / 2
  const fillPercent = Math.max(LIQUID_GAUGE_CONFIG.minValue, Math.min(LIQUID_GAUGE_CONFIG.maxValue, props.value))/LIQUID_GAUGE_CONFIG.maxValue

  const circleThickness = LIQUID_GAUGE_CONFIG.circleThickness * radius
  const circleFillGap = LIQUID_GAUGE_CONFIG.circleFillGap * radius
  const fillCircleMargin = circleThickness + circleFillGap

  return (
    <GaugeContainer
      className={props.className}
      type={props.type}
      width={parseInt(props.width)}
      height={parseInt(props.height)}
      radius={radius}
    >
      <GaugeBorder
        radius={radius}
        thickness={circleThickness}
      />

      <GaugeText
        className="gauge-text"
        value={props.value}
        margin={fillCircleMargin}
        radius={radius}
        preFilled={props.preFilled}
      />

      <GaugeLiquid
        id={props.id}
        fillPercent={fillPercent}
        radius={radius}
        margin={fillCircleMargin}
        preFilled={props.preFilled}
        wave={props.wave}
      >
        <GaugeText
          className="gauge-liquid-text"
          value={props.value}
          margin={fillCircleMargin}
          radius={radius}
          preFilled={props.preFilled}
        />
      </GaugeLiquid>
    </GaugeContainer>
  )
}

LiquidGauge.propTypes = {
  /**
   * An unique identifier for the Gauge.
   */
  id: T.string.isRequired,

  className: T.string,

  /**
   * The type of the Gauge (to apply correct color scheme).
   */
  type: T.oneOf(['primary', 'success', 'warning', 'danger', 'info', 'user']),

  /**
   * The available width for the Gauge.
   */
  width: T.number,

  /**
   * The available height for the Gauge.
   */
  height: T.number,


  /**
   * The current value.
   */
  value: T.number,

  preFilled: T.bool,

  /**
   * Makes the liquid wave.
   */
  wave: T.bool
}

LiquidGauge.defaultProps = {
  type: 'primary',
  width: 80,
  height: 80,
  value: 0,
  preFilled: false,
  wave: true
}

export {
  LiquidGauge
}
