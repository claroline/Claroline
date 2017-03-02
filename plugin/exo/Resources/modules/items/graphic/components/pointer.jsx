import React, {Component, PropTypes as T} from 'react'
import Popover from 'react-bootstrap/lib/Popover'
import Overlay from 'react-bootstrap/lib/Overlay'
import classes from 'classnames'
import {POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG} from './../enums'

const POINTER_WIDTH = 32
const SEGMENT_WIDTH = 6

export class Pointer extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

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
        className={classes('pointer', this.props.type)}
        style={{
          position: 'absolute',
          width: POINTER_WIDTH + 'px',
          height: POINTER_WIDTH + 'px',
          top: this.props.y - POINTER_WIDTH / 2,
          left: this.props.x - POINTER_WIDTH / 2,
          cursor: 'inherit'
        }}
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
          <span className={classes('fa', 'pointer-status-icon', {
            'fa-check': this.props.type === POINTER_CORRECT,
            'fa-times': this.props.type === POINTER_WRONG
          })}/>
        }

        {this.props.type !== POINTER_PLACED && this.props.feedback &&
          <span>
            <span
              ref={el => this.feedbackButton = el}
              onMouseOver={() => this.setState({showFeedback: true})}
              onMouseLeave={() => this.setState({showFeedback: false})}
              className="fa fa-comments-o pointer-feedback-btn"
            />
            <Overlay
              show={this.state.showFeedback}
              placement="top"
              container={this}
              target={this.feedbackButton}
            >
              <Popover
                id={`${this.props.x}${this.props.y}`}
                className="feedback-popover"
              >
                <span dangerouslySetInnerHTML={{ __html: this.props.feedback}}/>
              </Popover>
            </Overlay>
          </span>
        }
      </div>
    )
  }
}

Pointer.propTypes = {
  x: T.number.isRequired,
  y: T.number.isRequired,
  feedback: T.string,
  type: T.oneOf([POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG])
}
