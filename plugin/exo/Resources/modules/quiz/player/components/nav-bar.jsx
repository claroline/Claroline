import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t, tex} from '#/main/core/translation'

const PreviousButton = props =>
  <button className="btn btn-previous btn-default" onClick={props.onClick}>
    <span className="fa fa-fw fa-angle-double-left"></span>
    {t('previous')}
  </button>

PreviousButton.propTypes = {
  onClick: T.func.isRequired
}

const NextButton = props =>
  <button className="btn btn-next btn-default" onClick={props.onClick}>
    {t('next')}
    <span className="fa fa-fw fa-angle-double-right"></span>
  </button>

NextButton.propTypes = {
  onClick: T.func.isRequired
}

const ValidateButton = props =>
  <button className="btn btn-next btn-validate btn-default" onClick={props.onClick}>
    {t('validate')}
    <span className="fa fa-fw fa-angle-double-right"></span>
  </button>

ValidateButton.propTypes = {
  onClick: T.func.isRequired
}

const SubmitButton = props =>
  <button className="btn btn-submit btn-success" onClick={props.onClick}>
    <span className="fa fa-fw fa-check"></span>
    {t('validate')}
  </button>

SubmitButton.propTypes = {
  onClick: T.func.isRequired
}

const FinishButton = props =>
  <button className="btn btn-finish btn-primary" onClick={props.onClick}>
    <span className="fa fa-fw fa-sign-out"></span>
    {tex('finish')}
  </button>

FinishButton.propTypes = {
  onClick: T.func.isRequired
}

const canGoForward = (step, answers, mandatoryQuestions) => {
  const items = step.items
  let canGoForward = true

  items.forEach(item => {
    let hasAnswer = Boolean(answers[item.id] && answers[item.id].data)
    let goForward = mandatoryQuestions ?
      item.meta.mandatory ? true: hasAnswer:
      item.meta.mandatory ? hasAnswer: true

    if (!goForward) {
      canGoForward = false
    }
  })

  return canGoForward
}

const ForwardButton = props =>
(canGoForward(props.step, props.answers, props.mandatoryQuestions)) ?
    (props.next) ?
      <NotLastQuestionButton
        openFeedbackAndValidate={props.openFeedbackAndValidate}
        navigateToAndValidate={props.navigateToAndValidate}
        step={props.step}
        next={props.next}
        currentStepSend={props.currentStepSend}
        feedbackEnabled={props.feedbackEnabled}
        showFeedback={props.showFeedback}
      />:
      //no next section
      <LastQuestionButton
        openFeedbackAndValidate={props.openFeedbackAndValidate}
        finish={props.finish}
        currentStepSend={props.currentStepSend}
        feedbackEnabled={props.feedbackEnabled}
        showFeedback={props.showFeedback}
        step={props.step}
      />
    :
  <span/>

ForwardButton.propTypes = {
  next: T.object,
  step: T.object.isRequired,
  navigateToAndValidate: T.func.isRequired,
  finish: T.func.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,
  mandatoryQuestions: T.bool.isRequired,
  answers: T.array
}

const LastQuestionButton = props =>
  (props.showFeedback) ?
    (!props.feedbackEnabled) ?
      (props.currentStepSend) ?
        <ValidateButton onClick={() => props.openFeedbackAndValidate(props.step)} /> :
        <NextButton onClick={() => props.openFeedbackAndValidate(props.step)} /> :
      <FinishButton onClick={props.finish}/> :
    <FinishButton onClick={props.finish}/>

LastQuestionButton.propTypes = {
  step: T.object.isRequired,
  finish: T.func.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired
}

const NotLastQuestionButton = props =>
  (props.currentStepSend) ?
    (props.showFeedback) ?
      (!props.feedbackEnabled) ?
        <ValidateButton onClick={() => props.openFeedbackAndValidate(props.step)} /> :
        <NextButton onClick={() => props.navigateToAndValidate(props.next)} /> :
      <ValidateButton onClick={() => props.navigateToAndValidate(props.next)} /> :
    <NextButton onClick={() => props.navigateToAndValidate(props.next)} />

NotLastQuestionButton.propTypes = {
  step: T.object.isRequired,
  next: T.object,
  openFeedbackAndValidate: T.func.isRequired,
  navigateToAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired
}

const PlayerNav = props =>
  <nav className="player-nav">
    <div className="backward">
      {(props.previous) &&
        <PreviousButton onClick={() => props.navigateTo(props.previous)} />
      }
    </div>

    <div className="forward">
      <ForwardButton
        openFeedbackAndValidate={props.openFeedbackAndValidate}
        navigateToAndValidate={props.navigateToAndValidate}
        mandatoryQuestions={props.mandatoryQuestions}
        finish={props.finish}
        step={props.step}
        next={props.next}
        currentStepSend={props.currentStepSend}
        feedbackEnabled={props.feedbackEnabled}
        showFeedback={props.showFeedback}
        answers={props.answers}
      />
    </div>
  </nav>

PlayerNav.propTypes = {
  next: T.object,
  previous: T.object,
  step: T.object.isRequired,
  navigateTo: T.func.isRequired,
  finish: T.func.isRequired,
  navigateToAndValidate: T.func.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  submit: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,
  answers: T.array.isRequired,
  mandatoryQuestions: T.bool.isRequired
}

PlayerNav.defaultProps = {
  previous: null,
  next: null
}

export {PlayerNav}
