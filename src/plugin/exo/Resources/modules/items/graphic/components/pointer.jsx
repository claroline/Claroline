import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG} from '#/plugin/exo/items/graphic/constants'

const POINTER_WIDTH = 38
const SEGMENT_WIDTH = 5

class Pointer extends Component {

  render() {
    if (this.props.x < 0 || this.props.y < 0) {
      return null
    }

    const segmentOffset = POINTER_WIDTH / 2 - SEGMENT_WIDTH / 2
    const segments = [
      [segmentOffset, 0, SEGMENT_WIDTH, segmentOffset, 'n'],
      [segmentOffset + SEGMENT_WIDTH, segmentOffset, segmentOffset, SEGMENT_WIDTH, 'e'],
      [segmentOffset, segmentOffset + SEGMENT_WIDTH, SEGMENT_WIDTH, segmentOffset, 's'],
      [0, segmentOffset, segmentOffset, SEGMENT_WIDTH, 'w']
    ]

    return (
      <div
        className={classes('pointer', this.props.type, {
          'cursor-pointer': !!this.props.onClick
        })}
        style={{
          position: 'absolute',
          width: POINTER_WIDTH + 'px',
          height: POINTER_WIDTH + 'px',
          top: this.props.y - POINTER_WIDTH / 2,
          left: this.props.x - POINTER_WIDTH / 2,
          cursor: 'inherit'
        }}
        onClick={this.props.onClick}
      >
        {segments.map(s =>
          <span
            key={`${s[0]}${s[1]}${s[2]}${s[3]}`}
            className={classes('segment', s[4])}
            style={{
              position: 'absolute',
              left: s[0],
              top: s[1],
              width: s[2],
              height: s[3]
            }}
          />
        )}

        {this.props.type !== POINTER_PLACED &&
          <div className="pointer-status-icon position-absolute top-0 start-0 translate-middle">
            <span className={classes('fa', {
              'fa-check': this.props.type === POINTER_CORRECT,
              'fa-times': this.props.type === POINTER_WRONG
            })}/>
          </div>
        }
      </div>
    )
  }
}

Pointer.propTypes = {
  x: T.number.isRequired,
  y: T.number.isRequired,
  feedback: T.string,
  type: T.oneOf([POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG]),
  onClick: T.func
}

export {
  Pointer
}
