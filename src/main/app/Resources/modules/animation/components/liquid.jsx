import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import 'd3-transition'
import {easeLinear} from 'd3-ease'
import {scaleLinear} from 'd3-scale'
import {select} from 'd3-selection'
import {area} from 'd3-shape'


// largely inspired from http://bl.ocks.org/brattonc/5e5ce9beee483220e2f6

const LIQUID_CONFIG = {
  // The wave height as a percentage of the radius of the wave circle.
  waveHeight: 0.1,
  // The number of full waves per width of the wave circle.
  waveCount: 0.85,
  // The amount of time in milliseconds for the wave to rise from 0 to it's final height.
  waveRiseTime: 2000,
  // The amount of time in milliseconds for a full wave to enter the wave circle.
  waveAnimateTime: 2600,
  // The amount to initially offset the wave. 0 = no offset. 1 = offset of one full wave.
  waveOffset: 0
}

/**
 * Renders and animates a liquid circle.
 *
 * @todo : finish clean code
 * @todo : optimize
 */
class Liquid extends Component {
  constructor(props) {
    super(props)

    // pre calculate things that don't change at runtime (aka not linked to current value)
    const waveLength = (this.props.radius - this.props.margin) * 2 / LIQUID_CONFIG.waveCount
    this.waveClipCount = 1 + LIQUID_CONFIG.waveCount
    this.waveClipWidth = waveLength * this.waveClipCount

    this.waveGroupXPosition = this.props.margin + (this.props.radius - this.props.margin) * 2 - this.waveClipWidth

    // Controls wave size scaling at low and high fill percentages.
    // Wave height reaches it's maximum at 50% fill, and minimum at 0% and 100% fill.
    // This helps to prevent the wave from making the wave circle from appear totally full or empty when near it's minimum or maximum fill.
    this.waveHeightScale = scaleLinear()
      .range([0, LIQUID_CONFIG.waveHeight, 0])
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

  componentDidUpdate(prevProps) {
    if (prevProps.fillPercent !== this.props.fillPercent) {
      this.fill(prevProps.fillPercent, this.props.fillPercent)
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
        .duration(LIQUID_CONFIG.waveRiseTime)
        .attr('transform', `translate(${this.waveGroupXPosition}, ${waveRiseScale(newValue)})`)
        // This transform is necessary to get the clip wave positioned correctly when waveRise=true and waveAnimate=false.
        // The wave will not position correctly without this, but it's not clear why this is actually necessary.
        .on('start', () => wave.attr('transform', 'translate(1, 0)'))
        .on('end', () => {
          if (this.props.onFilled) {
            this.props.onFilled()
          }
        })
    } else {
      waveDef
        .attr('transform', `translate(${this.waveGroupXPosition}, ${waveRiseScale(newValue)})`)
    }
  }

  animate() {
    this.waveNode
      .attr('transform', `translate(${this.waveAnimation(this.waveNode.attr('delta'))}, 0)`)
      .transition()
      .duration(LIQUID_CONFIG.waveAnimateTime * (1-this.waveNode.attr('delta')))
      .ease(easeLinear)
      .attr('transform', `translate(${this.waveAnimation(1)}, 0)`)
      .attr('delta', 1)
      .on('end', () => {
        this.waveNode.attr('delta', 0)

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
      .y0(d => waveScaleY(Math.sin(Math.PI*2*LIQUID_CONFIG.waveOffset*-1 + Math.PI*2*(1-LIQUID_CONFIG.waveCount) + d.y*2*Math.PI)))
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
              delta={0}
            />
          </clipPath>
        </defs>

        <circle
          style={this.props.color ? {fill: this.props.color} : undefined}
          className={this.props.className}
          cx={this.props.radius}
          cy={this.props.radius}
          r={this.props.radius - this.props.margin}
        />

        {this.props.children}
      </g>
    )
  }
}

Liquid.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  fillPercent: T.number.isRequired,
  radius: T.number.isRequired,
  margin: T.number.isRequired,
  preFilled: T.bool.isRequired,
  onFilled: T.func,
  wave: T.bool.isRequired,
  children: T.node,
  color: T.string
}

Liquid.defaultProps = {
  margin: 0
}

export {
  Liquid
}
