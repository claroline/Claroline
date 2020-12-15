import React, {Component} from 'react'
import random from 'lodash/random'

import 'd3-transition'
import {easeElastic} from 'd3-ease'
import {select} from 'd3-selection'

const COMPASS_CONFIG = {
  // The amount of time to flip the hourglass
  flipTime: 2000
}

class Compass extends Component {
  constructor(props) {
    super(props)

    this.rotate = this.rotate.bind(this)
  }

  componentDidMount() {
    // avoid reselect nodes at each animation update
    this.arrowsNode = select(this.arrows)

    this.rotate()
  }

  componentWillUnmount() {

  }

  rotate(previousPos = 0) {
    const animation = easeElastic
      .amplitude(1.2)
      .period(0.45)

    const max = random(90, 360)

    // flip hourglass
    this.arrowsNode
      .transition()
      .duration(COMPASS_CONFIG.flipTime)
      .ease(animation)
      .attrTween('transform', () => {
        return (d) => `rotate(${previousPos + d*max}, 400, 460)`
      })
      .delay(500)
      .on('end', () => {
        this.arrowsNode
          .transition()
          .duration(COMPASS_CONFIG.flipTime)
          .ease(animation)
          .attrTween('transform', () => {
            return (d) => `rotate(${(previousPos + max)-d*max}, 400, 460)`
          })
          .delay(500)
          .on('end', () => this.rotate(max))
      })
  }

  render() {
    return (
      <svg viewBox="0 0 800 800" className="compass">
        <rect className="compass-fill" x="370" y="87" width="60" height="30"/>
        <circle className="compass-border" fill="none" strokeWidth="30" strokeMiterlimit="10" cx="400" cy="55" r="45"/>
        <circle className="compass-border" fill="none" strokeWidth="30" strokeMiterlimit="10" cx="400" cy="460" r="330"/>
        <circle className="compass-border" fill="none" strokeWidth="6" strokeMiterlimit="10" cx="400" cy="460" r="210"/>
        <circle className="compass-fill" cx="400" cy="460" r="40"/>
        <g className="compass-fill">
          <polygon points="74,490 74,430 134,460"/>
          <polygon points="726,490 726,430 666,460"/>
          <polygon points="430,786 370,786 400,726"/>
          <polygon points="430,134 370,134 400,194"/>
        </g>

        <g ref={element => this.arrows = element}>
          <g className="primary">
            <polygon className="compass-arrow" points="460,460.1 460,460.1 460,460.1 	"/>
            <path className="compass-arrow" d="M400,520c-23.3,0-43.6-13.3-53.5-32.8L400,710l53.5-222.8C443.6,506.6,423.4,520,400,520z"/>
          </g>

          <g className="secondary">
            <path className="compass-arrow" d="M400,400c23.4,0,43.6,13.4,53.5,32.8L400,210l-53.4,222.8C356.4,413.3,376.7,400,400,400z"/>
            <polygon className="compass-arrow" points="460,459.9 460,459.9 460,459.9 	"/>
          </g>
        </g>
      </svg>
    )
  }
}

export {
  Compass
}
