import React, {Component, PropTypes as T} from 'react'
import {connect} from 'react-redux'
import {actions} from './../actions'
import {selectors as correctionSelectors} from './../selectors'
import {tex} from './../../../utils/translate'
import Panel from 'react-bootstrap/lib/Panel'
import FormGroup from 'react-bootstrap/lib/FormGroup'
import InputGroup from 'react-bootstrap/lib/InputGroup'
import FormControl from 'react-bootstrap/lib/FormControl'
import {Textarea} from './../../../components/form/textarea.jsx'
import {TooltipButton} from './../../../components/form/tooltip-button.jsx'

class AnswerRow extends Component {
  constructor(props) {
    super(props)
    this.state = {showFeedback: false}
  }

  render() {
    return (
      <div>
        <div className="row answer-row">
          <Panel className="answer-panel" key={this.props.id}>
            <div dangerouslySetInnerHTML={{__html: this.props.data}}></div>
          </Panel>
          <div className="score-controls">
            <FormGroup validationState={this.props.score && (isNaN(this.props.score) || this.props.score > this.props.scoreMax) ? 'error' : null}>
              <InputGroup className="score-input">
                <FormControl key={this.props.id}
                             type="text"
                             value={this.props.score !== undefined && this.props.score !== null ? this.props.score : ''}
                             onChange={(e) => this.props.updateScore(this.props.id, e.target.value)}
                />
                <InputGroup.Addon>/{this.props.scoreMax}</InputGroup.Addon>
              </InputGroup>
            </FormGroup>
            <TooltipButton id={`feedback-${this.props.id}-toggle`}
                           className="fa fa-comments-o"
                           title={tex('feedback')}
                           onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            />
          </div>
        </div>
        {this.state.showFeedback &&
          <div className="row feedback-row">
            <Textarea
              id={`feedback-${this.props.id}-data`}
              title={tex('response')}
              content={this.props.feedback ? `${this.props.feedback}` : ''}
              onChange={(text) => this.props.updateFeedback(this.props.id, text)}
            />
          </div>
        }
        <div className="row">
          <hr/>
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
    <h4 dangerouslySetInnerHTML={{__html: props.question.content}}></h4>
    {props.answers.length > 0 ?
      props.answers.map((answer, idx) =>
        <AnswerRow key={idx}
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
  question: T.object.isRequired,
  answers: T.arrayOf(T.object).isRequired,
  updateScore: T.func.isRequired,
  updateFeedback: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    question: correctionSelectors.currentQuestion(state),
    answers: correctionSelectors.answers(state)
  }
}

const ConnectedAnswers = connect(mapStateToProps, actions)(Answers)

export {ConnectedAnswers as Answers}