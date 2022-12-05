import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'
import Overlay from 'react-bootstrap/lib/Overlay'

import {trans, transChoice} from '#/main/app/intl/translation'

import {ContentHtml} from '#/main/app/content/components/html'

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
            <ContentHtml>{this.props.feedback}</ContentHtml>
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
  <div
    className="answers-table"
    style={{
      width: '60%',
      margin: 'auto'
    }}
  >
    <h3 className="title">{props.title}</h3>

    {props.showLegend &&
      <div className="well well-sm">
        <div>
          {trans('correct_zone_desc', {}, 'quiz')}
        </div>
        <div>
          {trans('tolerance_zone_desc', {}, 'quiz')}
        </div>
      </div>
    }

    {props.sections.map((section) =>
      <div
        key={section.id}
        className={classes('answer-row', {
          'correct-answer': props.highlightScore && section.score > 0,
          'incorrect-answer': props.highlightScore && section.score <= 0
        })}
        style={{
          minHeight: '34px',
          border: 'solid 1px #DDDDDD',
          padding: '6px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}
      >
        <span
          className="info-block"
          style={{
            display: 'flex',
            alignItems: 'center'
          }}
        >
          {props.highlightScore &&
            <span
              className={classes('fa fa-fw', 'section-status-icon', {
                'fa-check': section.score > 0,
                'fa-times': section.score <= 0
              })}
              style={{
                marginRight: '2px'
              }}
            />
          }
          <input
            className="form-control"
            type="text"
            disabled={true}
            value={section.start}
            style={{
              maxWidth: '100px',
              marginRight: '2px',
              marginLeftt: '2px'
            }}
          />
          <input
            className="form-control"
            type="text"
            disabled={true}
            value={section.end}
            style={{
              maxWidth: '100px',
              marginRight: '2px',
              marginLeftt: '2px'
            }}
          />
        </span>
        <span className="info-block">
          {section.feedback &&
            <HoverFeedback
              id={`${section.id}-popover`}
              feedback={section.feedback}
            />
          }
          {props.showScore &&
            <span className="solution-score">
              {transChoice('solution_score', section.score, {score: section.score}, 'quiz')}
            </span>
          }
        </span>
      </div>
    )}
  </div>

AnswerTable.propTypes = {
  highlightScore: T.bool.isRequired,
  title: T.string.isRequired,
  sections: T.arrayOf(T.shape({
    id: T.string.isRequired,
    score: T.number,
    feedback: T.string
  })).isRequired,
  showScore: T.bool.isRequired,
  showLegend: T.bool.isRequired
}

AnswerTable.defaultProps = {
  showLegend: false
}

export {
  AnswerTable
}
