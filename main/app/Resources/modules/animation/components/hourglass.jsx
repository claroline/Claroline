import React, {Component} from 'react'
import classes from 'classnames'

import 'd3-transition'
import {easeLinear, easeElastic} from 'd3-ease'
import {select} from 'd3-selection'

import {Liquid} from '#/main/app/animation/components/liquid'

const HOURGLASS_CONFIG = {
  // The amount of time to flip the hourglass
  flipTime: 2000,
  flowSpeed: 5
}

class Hourglass extends Component {
  constructor(props) {
    super(props)

    this.state = {
      flipped: false,
      flowed: 0
    }

    this.flip = this.flip.bind(this)
    this.transferLiquid = this.transferLiquid.bind(this)
  }

  componentDidMount() {
    // avoid reselect nodes at each animation update
    this.hourglassNode = select(this.hourglass)

    this.liquidSprayNode = select(this.liquidSpray)
    this.topLiquidNode = select(this.topLiquid)
    this.bottomLiquidNode = select(this.bottomLiquid)

    this.transferLiquid()
  }

  flip() {
    const animation = easeElastic
      .amplitude(1.2)
      .period(0.45)

    // flip hourglass
    this.hourglassNode
      .transition()
      .duration(HOURGLASS_CONFIG.flipTime)
      .ease(animation)
      .attrTween('transform', () => {
        return (d) => `rotate(${this.state.flipped ? (d*180)+180 : d*180}, 400, 400)`
      })

    this.topLiquidNode
      .transition()
      .duration(HOURGLASS_CONFIG.flipTime)
      .ease(animation)
      .attrTween('transform', () => {
        return (d) => `translate(230 30) rotate(${-(this.state.flipped ? (d*180)+180 : d*180)}, 170, 170)`
      })

    this.bottomLiquidNode
      .transition()
      .duration(HOURGLASS_CONFIG.flipTime)
      .ease(animation)
      .attrTween('transform', () => {
        return (d) => `translate(230 430) rotate(${-(this.state.flipped ? (d*180)+180 : d*180)}, 170, 170)`
      })
      .on('end', () => {
        this.setState({flipped: !this.state.flipped})

        // start liquid flow
        this.transferLiquid()
      })
  }

  transferLiquid() {
    this.liquidFlow = setInterval(() => {
      this.liquidSprayNode
        .append('circle')
        .attr('class', 'hourglass-liquid')
        .attr('cx', 400)
        .attr('cy', this.state.flipped ? 420 : 380)
        // random radius between 25-50px
        .attr('r', Math.floor((1+Math.random())*25))
        .transition()
        .delay(50)
        .duration(200)
        .ease(easeLinear)
        .attrTween('transform', () => {

          return (d) => {
            let x = d*40
            if (Math.round(Math.random())) {
              x = -x
            }

            let y = d*340
            if (this.state.flipped) {
              y = -y
            }

            return `translate(${x} ${y})`
          }
        })

      let flowed = this.state.flowed
      if (this.state.flipped) {
        flowed -= HOURGLASS_CONFIG.flowSpeed
      } else {
        flowed += HOURGLASS_CONFIG.flowSpeed
      }

      if (75 >= flowed && 0 <= flowed) {
        // continue to transfer liquid
        this.setState({flowed: flowed})
      } else {
        this.liquidSprayNode.selectAll('circle').remove()

        clearInterval(this.liquidFlow)
        // no more liquid to flow, flip the hourglass
        setTimeout(this.flip, 100)
      }
    }, 100)
  }

  componentWillUnmount() {
    clearInterval(this.liquidFlow)
  }

  render() {
    return (
      <svg viewBox="0 0 800 800" className="hourglass">
        <g ref={element => this.hourglass = element} className={classes({
          primary: !this.state.flipped,
          secondary: this.state.flipped
        })}>
          <g ref={element => this.liquidSpray = element} />

          <g transform="translate(230 30)" ref={element => this.topLiquid = element}>
            <Liquid
              id="top-liquid"
              className="hourglass-liquid"
              fillPercent={(75 - this.state.flowed) / 100}
              radius={170}
              preFilled={true}
              wave={true}
            />
          </g>

          <g transform="translate(230 430)" ref={element => this.bottomLiquid = element}>
            <Liquid
              id="bottom-liquid"
              className="hourglass-liquid"
              fillPercent={this.state.flowed / 100}
              radius={170}
              preFilled={true}
              wave={true}
            />
          </g>

          <path
            className="hourglass-cap"
            d="M587,774H213c-3.3,0-6-2.7-6-6v-28c0-3.3,2.7-6,6-6h374c3.3,0,6,2.7,6,6v28C593,771.3,590.3,774,587,774z"
          />

          <path
            className="hourglass-border"
            fill="none"
            strokeWidth="20"
            strokeMiterlimit="10"
            d="M569.7,740c31.4-38,50.3-86.8,50.3-140c0-63.5-26.9-120.8-70-160.9v0c-12.4-8.6-20.5-22.9-20.5-39.1s8.1-30.5,20.5-39.1v0c43.1-40.2,70-97.4,70-160.9c0-53.2-18.9-102-50.3-140"
          />

          <path
            className="hourglass-border"
            fill="none"
            strokeWidth="20"
            strokeMiterlimit="10"
            d="M230.3,60C198.9,98,180,146.8,180,200c0,63.5,26.9,120.8,70,160.9v0c12.4,8.6,20.5,22.9,20.5,39.1s-8.1,30.5-20.5,39.1v0c-43.1,40.2-70,97.4-70,160.9c0,53.2,18.9,102,50.3,140"
          />

          <path
            className="hourglass-cap"
            d="M587,66H213c-3.3,0-6-2.7-6-6V32c0-3.3,2.7-6,6-6h374c3.3,0,6,2.7,6,6v28C593,63.3,590.3,66,587,66z"
          />
        </g>
      </svg>
    )
  }
}

export {
  Hourglass
}
