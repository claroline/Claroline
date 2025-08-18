import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/Popover'
import Overlay from 'react-bootstrap/Overlay'

import {SHAPE_RECT} from '#/plugin/exo/items/graphic/constants'
import {SolutionScore} from '#/plugin/exo/components/score'
import {Html} from '#/main/app/components/html'
import {FeedbackButton} from '#/plugin/exo/buttons/feedback/components/button'

class HoverFeedback extends Component {
  constructor(props) {
    super(props)
    this.state = {
      show: false
    }
  }

  render() {
    return (
      <span style={{position: 'relative'}}>
        <span
          ref={el => this.el = el}
          className="fa fa-fw fa-comments"
          onMouseOver={() => this.setState({show: true})}
          onMouseLeave={() => this.setState({show: false})}
        />
        <Overlay
          show={this.state.show}
          placement="top"
          container={this}
          target={this.el}
        >
          <Popover
            id={this.props.id}
            className="feedback-popover"
          >
            <Html>{this.props.feedback}</Html>
          </Popover>
        </Overlay>
      </span>
    )
  }
}

HoverFeedback.propTypes = {
  id: T.string.isRequired,
  feedback: T.string.isRequired
}

const AnswerTable = props =>
  <div className="mt-4">
    {props.areas.map((area, idx) =>
      <div key={area.id} className={classes('graphic-answer-item answer-item d-flex align-items-center', {
        'correct-answer': props.highlightScore && area.score > 0,
        'incorrect-answer': props.highlightScore && area.score <= 0,
        'selected-answer': !props.highlightScore && area.score > 0,
        'mb-0': idx === props.areas.length - 1
      })}>
        {props.highlightScore &&
          <span className={classes('graphic-item-tick fa fa-fw', {
            'fa-check': area.score > 0,
            'fa-times': area.score <= 0
          })}/>
        }

        <div className="flex-fill d-flex gap-2 align-items-center">
          <span className="d-inline-block" style={{
            display: 'inline-block',
            width: '1.5rem',
            height: '1.5rem',
            backgroundColor: area.color || '#000',
            borderRadius: area.shape === SHAPE_RECT ? 0 : '50rem'
          }}/>
          <strong>{idx + 1}</strong>
        </div>

        {area.feedback &&
          <FeedbackButton
            id={area.id}
            feedback={area.feedback}
          />
        }

        {props.showScore &&
          <SolutionScore score={area.score} />
        }
      </div>
    )}
  </div>

AnswerTable.propTypes = {
  highlightScore: T.bool.isRequired,
  title: T.string.isRequired,
  areas: T.arrayOf(T.shape({
    id: T.string.isRequired,
    score: T.number,
    color: T.string.isRequired,
    shape: T.string.isRequired,
    feedback: T.string
  })).isRequired,
  showScore: T.bool.isRequired
}

export {
  AnswerTable
}
