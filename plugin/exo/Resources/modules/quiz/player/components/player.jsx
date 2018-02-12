import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Panel from 'react-bootstrap/lib/Panel'

import {tex} from '#/main/core/translation'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'

import {getDefinition, isQuestionType} from './../../../items/item-types'
import {getContentDefinition} from './../../../contents/content-types'
import selectQuiz from './../../selectors'
import {select} from './../selectors'
import {actions} from './../actions'
import {ItemPlayer} from './item-player.jsx'
import {ItemFeedback} from './item-feedback.jsx'
import {ContentItemPlayer} from './content-item-player.jsx'
import {PlayerNav} from './nav-bar.jsx'
import {CustomDragLayer} from './../../../utils/custom-drag-layer.jsx'
import {getNumbering} from './../../../utils/numbering'
import {NUMBERING_NONE} from './../../../quiz/enums'

const PlayerComponent = props =>
  <div className="quiz-player">
    <h2 className="step-title">
      {props.step.title ? props.step.title : tex('step') + ' ' + props.number}
    </h2>

    {props.step.description &&
      <div className="step-description" dangerouslySetInnerHTML={{ __html: props.step.description }}></div>
    }

    {props.items.map((item, index) => (
      <Panel key={item.id}>
        {!isQuestionType(item.type) ?
          <ContentItemPlayer
            item={item}
          >
            {React.createElement(getContentDefinition(item.type)['player'], {item: item})}
          </ContentItemPlayer>
          : (!props.feedbackEnabled ?
          <ItemPlayer
            item={item}
            showHint={(questionId, hint) => props.showHint(props.quizId, props.paper.id, questionId, hint)}
            usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
            numbering={props.numbering !== NUMBERING_NONE ? props.stepIndex + '.' + getNumbering(props.numbering, index): null}
          >
            {React.createElement(getDefinition(item.type).player, {
              item: item,
              answer: props.answers[item.id] && props.answers[item.id].data ? props.answers[item.id].data : undefined,
              onChange: (answerData) => props.updateAnswer(item.id, answerData)
            })}
          </ItemPlayer>
          :
          <ItemFeedback
            item={item}
            usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
            numbering={props.numbering !== NUMBERING_NONE ? props.stepIndex + '.' + getNumbering(props.numbering, index): null}
          >
            {React.createElement(getDefinition(item.type).feedback, {
              item: item,
              answer: props.answers[item.id] && props.answers[item.id].data ? props.answers[item.id].data : undefined
            })}
          </ItemFeedback>
        )}
      </Panel>
    ))}

    <PlayerNav
      previous={props.previous}
      mandatoryQuestions={props.mandatoryQuestions}
      next={props.next}
      step={props.step}
      answers = {props.answers}
      showFeedback={props.showFeedback}
      feedbackEnabled={props.feedbackEnabled}
      navigateTo={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, false)}
      navigateToAndValidate={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, props.currentStepSend)}
      openFeedbackAndValidate={(step) => props.navigateTo(props.quizId, props.paper.id, step, props.answers, props.currentStepSend, true)}
      submit={() => props.submit(props.quizId, props.paper.id, props.answers)}
      finish={() => props.finish(props.quizId, props.paper, props.answers, props.showFeedback, props.showEndConfirm)}
      currentStepSend={props.currentStepSend}
    />
    <CustomDragLayer />
  </div>

PlayerComponent.propTypes = {
  quizId: T.string.isRequired,
  numbering: T.string.isRequired,
  number: T.number.isRequired,
  stepIndex: T.number,
  step: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  items: T.array.isRequired,
  mandatoryQuestions: T.bool.isRequired,
  answers: T.object.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    number: T.number.isRequired
  }).isRequired,
  next: T.object,
  previous: T.object,
  showFeedback: T.bool.isRequired,
  showEndConfirm: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,

  updateAnswer: T.func.isRequired,
  navigateTo: T.func.isRequired,
  submit: T.func.isRequired,
  finish: T.func.isRequired,
  showHint: T.func.isRequired
}

PlayerComponent.defaultProps = {
  next: null,
  previous: null,
  answers: {}
}

const Player = connect(
  state => ({
    mandatoryQuestions: selectQuiz.parameters(state).mandatoryQuestions,
    quizId: selectQuiz.id(state),
    number: select.currentStepNumber(state),
    step: select.currentStep(state),
    items: select.currentStepItems(state),
    paper: select.paper(state),
    answers: select.currentStepAnswers(state),
    next: select.next(state),
    previous: select.previous(state),
    showFeedback: select.showFeedback(state),
    showEndConfirm: select.showEndConfirm(state),
    feedbackEnabled: select.feedbackEnabled(state),
    currentStepSend: select.currentStepSend(state),
    numbering: selectQuiz.quizNumbering(state),
    stepIndex: select.currentStepIndex(state)
  }),
  dispatch => ({
    updateAnswer(questionId, answerData) {
      dispatch(actions.updateAnswer(questionId, answerData))
    },
    navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback) {
      dispatch(actions.navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback))
    },
    submit(quizId, paperId, answers) {
      dispatch(actions.submit(quizId, paperId, answers))
    },
    finish(quizId, paper, pendingAnswers, showFeedback, showConfirm) {
      if (showConfirm) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          title: tex('finish_confirm_title'),
          question: tex('finish_confirm_question'),
          handleConfirm: () => dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback))
        }))
      } else {
        dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback))
      }
    },
    showHint(quizId, paperId, questionId, hint) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: tex('hint_confirm_title'),
        question: tex('hint_confirm_question'),
        handleConfirm: () => dispatch(actions.showHint(quizId, paperId, questionId, hint))
      }))
    }
  })
)(PlayerComponent)

export {
  Player
}
