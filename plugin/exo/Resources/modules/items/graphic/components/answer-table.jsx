import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import Popover from 'react-bootstrap/lib/Popover'
import Overlay from 'react-bootstrap/lib/Overlay'

import {transChoice} from '#/main/core/translation'

import {SHAPE_RECT} from './../enums'

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
          className="fa fa-fw fa-comments-o"
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
            <span dangerouslySetInnerHTML={{ __html: this.props.feedback}}/>
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
  <div className="answers-table">
    <h3 className="title">{props.title}</h3>
    {props.areas.map((area, idx) =>
      <div key={area.id} className={classes('answer-row', {
        'correct-answer': props.highlightScore && area.score > 0,
        'incorrect-answer': props.highlightScore && area.score <= 0
      })}>
        <span className="info-block">
          <span><strong>{idx + 1}</strong></span>
          <span style={{
            display: 'inline-block',
            width: '24px',
            height: '24px',
            backgroundColor: tinycolor(area.color).lighten(20).toString(),
            border: `solid 1px ${area.color}`,
            borderRadius: area.shape === SHAPE_RECT ? 0 : '12px'
          }}/>
        {props.highlightScore &&
          <span className={classes('fa fa-fw', 'area-status-icon', {
            'fa-check': area.score > 0,
            'fa-times': area.score <= 0
          })}/>
        }
        </span>
        <span className="info-block">
          {area.feedback &&
            <HoverFeedback
              id={`${area.id}-popover`}
              feedback={area.feedback}
            />
          }
          {props.showScore &&
            <span className="solution-score">
              {transChoice('solution_score', area.score, {score: area.score}, 'ujm_exo')}
            </span>
          }
        </span>
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
