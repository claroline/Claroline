import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {t, tex} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {actions} from './../actions'
import {selectors as correctionSelectors} from './../selectors'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

class AnswerRow extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    return (
      <div className="panel panel-default">
        <div className="user-answer panel-body">
          <div className="text-fields">
            {this.props.data && 0 !== this.props.data.length ?
              <HtmlText className="answer-item">{this.props.data}</HtmlText>
              :
              <div className="no-answer">{tex('no_answer')}</div>
            }

            {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`feedback-${this.props.id}-data`}
                title={tex('response')}
                content={this.props.feedback ? `${this.props.feedback}` : ''}
                onChange={(text) => this.props.updateFeedback(this.props.id, text)}
              />
            </div>
            }
          </div>

          <div className="right-controls">
            <span className="input-group score-input">
              <input
                type="number"
                className={classes('form-control', {
                  'has-error': this.props.score && (isNaN(this.props.score) || this.props.score > this.props.scoreMax)
                })}
                value={this.props.score !== undefined && this.props.score !== null ? this.props.score : ''}
                onChange={(e) => this.props.updateScore(this.props.id, e.target.value)}
              />
              <span className="input-group-addon">{`/ ${this.props.scoreMax}`}</span>
            </span>

            <TooltipButton
              id={`feedback-${this.props.id}-toggle`}
              className="btn-link-default"
              title={tex('feedback')}
              onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            >
              <span className="fa fa-fw fa-comments-o" />
            </TooltipButton>
          </div>
        </div>
      </div>
    )
  }
}

AnswerRow.propTypes = {
  id: T.string.isRequired,
  questionId: T.string.isRequired,
  data: T.string,
  score: T.string,
  scoreMax: T.number.isRequired,
  feedback: T.string,
  updateScore: T.func.isRequired,
  updateFeedback: T.func.isRequired
}

let Answers = props =>
  <div className="answers-list">
    <h2 className="question-title">
      {props.question.title || props.question.content}

      {props.answers.length > 0 &&
        <button
          type="button"
          className="btn btn-sm btn-primary"
          disabled={!props.saveEnabled}
          onClick={() => props.saveEnabled && props.saveCorrection(props.question.id)}
        >
          <span className="fa fa-fw fa-floppy-o"/>
          {t('save')}
        </button>
      }
    </h2>
    {props.answers.length > 0 ?
      props.answers.map((answer, idx) =>
        <AnswerRow
          key={idx}
          scoreMax={props.question.score && props.question.score.max}
          updateScore={props.updateScore}
          updateFeedback={props.updateFeedback}
          {...answer}
        />
      ) :
      <div className="alert alert-warning">
        {tex('no_answer_to_correct')}
      </div>
    }
  </div>

Answers.propTypes = {
  question: T.shape({
    id: T.string.isRequired,
    title: T.string,
    content: T.string.isRequired,
    score: T.object.isRequired
  }).isRequired,
  answers: T.arrayOf(T.object).isRequired,
  saveEnabled: T.bool.isRequired,
  updateScore: T.func.isRequired,
  updateFeedback: T.func.isRequired,
  saveCorrection: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    question: correctionSelectors.currentQuestion(state),
    answers: correctionSelectors.answers(state),
    saveEnabled: correctionSelectors.hasCorrection(state)
  }
}

const ConnectedAnswers = connect(mapStateToProps, actions)(Answers)

export {ConnectedAnswers as Answers}
