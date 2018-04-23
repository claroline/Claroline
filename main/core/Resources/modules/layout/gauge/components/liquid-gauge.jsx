import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import 'd3-transition'
import {easeLinear} from 'd3-ease'
import {scaleLinear} from 'd3-scale'
import {select} from 'd3-selection'
import {area} from 'd3-shape'

import {constants} from '#/main/core/layout/gauge/constants'
import {Gauge as GaugeTypes} from '#/main/core/layout/gauge/prop-types'
import {
  GaugeContainer,
  GaugeBorder,
  GaugeText
} from '#/main/core/layout/gauge/components/gauge'

// largely inspired from http://bl.ocks.org/brattonc/5e5ce9beee483220e2f6

const LIQUID_GAUGE_CONFIG = {
  // The gauge minimum value.
  minValue: 0,
  // The gauge maximum value.
  maxValue: 100,
  // The outer circle thickness as a percentage of it's radius.
  circleThickness: 0.08,
  // The size of the gap between the outer circle and wave circle as a percentage of the outer circles radius.
  circleFillGap: 0.06,
  // The wave height as a percentage of the radius of the wave circle.
  waveHeight: 0.1,
  // The number of full waves per width of the wave circle.
  waveCount: 0.85,
  // The amount of time in milliseconds for the wave to rise from 0 to it's final height.
  waveRiseTime: 2000,
  // The amount of time in milliseconds for a full wave to enter the wave circle.
  waveAnimateTime: 2600,
  // Controls wave size scaling at low and high fill percentages.
  // When true, wave height reaches it's maximum at 50% fill, and minimum at 0% and 100% fill.
  // This helps to prevent the wave from making the wave circle from appear totally full or empty when near it's minimum or maximum fill.
  waveHeightScaling: true,
  // The amount to initially offset the wave. 0 = no offset. 1 = offset of one full wave.
  waveOffset: 0,
  // The height at which to display the percentage text withing the wave circle. 0 = bottom, 1 = top.
  textVertPosition: constants.GAUGE_TEXT_POSITION,
  // The relative height of the text to display in the wave circle. 1 = 50%
  textSize: constants.GAUGE_TEXT_SIZE,
  // If true, a % symbol is displayed after the value.
  displayPercent: true
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
      className={classes('liquid-gauge', props.className)}
      type={props.type}
      width={parseInt(props.width)}
      height={parseInt(props.height)}
      radius={radius}
    >
      <GaugeBorder
        radius={radius}
        thickness={circleThickness}
        preFilled={true}
      />

      <GaugeText
        className="gauge-text"
        value={props.value}
        displayValue={props.displayValue}
        margin={fillCircleMargin}
        radius={radius}
        preFilled={props.preFilled}
        unit={props.unit}
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
          displayValue={props.displayValue}
          margin={fillCircleMargin}
          radius={radius}
          preFilled={props.preFilled}
          unit={props.unit}
        />
      </GaugeLiquid>
    </GaugeContainer>
  )
}

LiquidGauge.propTypes = merge({}, GaugeTypes.propTypes, {
  /**
   * An unique identifier for the Gauge.
   */
  id: T.string.isRequired,

  /**
   * Makes the liquid wave.
   */
  wave: T.bool
})

LiquidGauge.defaultProps = merge({}, GaugeTypes.defaultProps, {
  wave: true
})

export {
  LiquidGauge
}
