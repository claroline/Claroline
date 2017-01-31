import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import Panel from 'react-bootstrap/lib/Panel'

import {tex} from './../../../utils/translate'
import {getDefinition} from './../../../items/item-types'
import selectQuiz from './../../selectors'
import {select} from './../selectors'

import {actions as playerActions} from './../actions'

import {ItemPlayer} from './item-player.jsx'
import {PlayerNav} from './nav-bar.jsx'

const Player = props => {
  return(
    <div className="quiz-player">
      <h2 className="step-title">
        {props.step.title ? props.step.title : tex('step') + ' ' + props.number}
      </h2>

      {props.step.description &&
        <div className="step-description" dangerouslySetInnerHTML={{ __html: props.step.description }}></div>
      }

      {props.items.map((item) => (
        <Panel
          key={item.id}
        >
          <ItemPlayer
            item={item}
            showHint={(questionId, hint) => props.showHint(props.quizId, props.paper.id, questionId, hint)}
            usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
          >
            {React.createElement(getDefinition(item.type)[props.feedbackEnabled ? 'feedback': 'player'], {
              item: item,
              answer: props.answers[item.id] && props.answers[item.id].data ? props.answers[item.id].data : undefined,
              onChange: (answerData) => props.updateAnswer(item.id, answerData)
            })}
          </ItemPlayer>
        </Panel>
      ))}

      <PlayerNav
        previous={props.previous}
        next={props.next}
        step={props.step}
        showFeedback={props.showFeedback}
        feedbackEnabled={props.feedbackEnabled}
        navigateTo={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, false)}
        navigateToAndValidate={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, props.currentStepSend)}
        openFeedbackAndValidate={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, props.currentStepSend, true)}
        submit={() => props.submit(props.quizId, props.paper.id, props.answers)}
        finish={() => props.finish(props.quizId, props.paper, props.answers, props.showFeedback)}
        currentStepSend={props.currentStepSend}
      />
    </div>
  )
}

Player.propTypes = {
  quizId: T.string.isRequired,
  number: T.number.isRequired,
  step: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  items: T.array.isRequired,
  answers: T.object.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    number: T.number.isRequired
  }).isRequired,
  next: T.object,
  previous: T.object,
  updateAnswer: T.func.isRequired,
  navigateTo: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  submit: T.func.isRequired,
  finish: T.func.isRequired,
  showHint: T.func.isRequired,
  currentStepSend: T.bool.isRequired
}

Player.defaultProps = {
  next: null,
  previous: null
}

function mapStateToProps(state) {
  return {
    quizId: selectQuiz.id(state),
    number: select.currentStepNumber(state),
    step: select.currentStep(state),
    items: select.currentStepItems(state),
    paper: select.paper(state),
    answers: select.currentStepAnswers(state),
    next: select.next(state),
    previous: select.previous(state),
    showFeedback: select.showFeedback(state),
    feedbackEnabled: select.feedbackEnabled(state),
    currentStepSend: select.currentStepSend(state)
  }
}

const ConnectedPlayer = connect(mapStateToProps, playerActions)(Player)

export {ConnectedPlayer as Player}
