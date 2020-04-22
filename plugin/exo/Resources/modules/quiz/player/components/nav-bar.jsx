import React from 'react'
import {PropTypes as T} from 'prop-types'
import {isQuestionType} from '#/plugin/exo/items/item-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'

// TODO : replace all button by #/main/app/action/components/button

const PreviousButton = props =>
  <button
    className="btn btn-previous btn-default btn-emphasis"
    onClick={(e) => {
      props.onClick(e)
      scrollTo(`#resource-${props.resourceId} > .page-content`)
    }}
  >
    <span className="fa fa-fw fa-angle-double-left icon-with-text-right" />
    {trans('previous')}
  </button>

PreviousButton.propTypes = {
  resourceId: T.string.isRequired,
  onClick: T.func.isRequired
}

const NextButton = props =>
  <button
    className="btn btn-next btn-default btn-emphasis"
    onClick={(e) => {
      props.onClick(e)
      scrollTo(`#resource-${props.resourceId} > .page-content`)
    }}
  >
    {trans('next')}
    <span className="fa fa-fw fa-angle-double-right icon-with-text-left" />
  </button>

NextButton.propTypes = {
  resourceId: T.string.isRequired,
  onClick: T.func.isRequired
}

const ValidateButton = props =>
  <button
    className="btn btn-next btn-validate btn-default btn-emphasis"
    onClick={(e) => {
      props.onClick(e)
      scrollTo(`#resource-${props.resourceId} > .page-content`)
    }}
  >
    {trans('validate')}
    <span className="fa fa-fw fa-angle-double-right icon-with-text-left" />
  </button>

ValidateButton.propTypes = {
  resourceId: T.string.isRequired,
  onClick: T.func.isRequired
}

const SubmitButton = props =>
  <button
    className="btn btn-submit btn-success btn-emphasis"
    onClick={(e) => {
      props.onClick(e)
      scrollTo(`#resource-${props.resourceId} > .page-content`)
    }}
  >
    <span className="fa fa-fw fa-check icon-with-text-right" />
    {trans('validate')}
  </button>

SubmitButton.propTypes = {
  resourceId: T.string.isRequired,
  onClick: T.func.isRequired
}

const FinishButton = props =>
  <button
    className="btn btn-finish btn-primary btn-emphasis"
    onClick={(e) => {
      props.onClick(e)
      scrollTo(`#resource-${props.resourceId} > .page-content`)
    }}
  >
    <span className="fa fa-fw fa-sign-out icon-with-text-right" />
    {trans('finish', {}, 'actions')}
  </button>

FinishButton.propTypes = {
  resourceId: T.string.isRequired,
  onClick: T.func.isRequired
}

const canGoForward = (step, answers, mandatoryQuestions) => {
  const items = step.items
  let canGoForward = true

  items.forEach(item => {
    let hasAnswer = Boolean(answers[item.id] && answers[item.id].data)

    let goForward = true
    if (isQuestionType(item.type)) {
      goForward = mandatoryQuestions ? (item.meta.mandatory ? true : hasAnswer) : (item.meta.mandatory ? hasAnswer: true)
    }

    if (!goForward) {
      canGoForward = false
    }
  })

  return canGoForward
}

const ForwardButton = props => canGoForward(props.step, props.answers, props.mandatoryQuestions) ?
  (props.next) ?
    <NotLastQuestionButton
      resourceId={props.resourceId}
      openFeedbackAndValidate={props.openFeedbackAndValidate}
      navigateToAndValidate={props.navigateToAndValidate}
      step={props.step}
      next={props.next}
      currentStepSend={props.currentStepSend}
      feedbackEnabled={props.feedbackEnabled}
      showFeedback={props.showFeedback}
    /> :
    //no next section
    <LastQuestionButton
      resourceId={props.resourceId}
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
  resourceId: T.string.isRequired,
  navigateToAndValidate: T.func.isRequired,
  finish: T.func.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,
  mandatoryQuestions: T.bool.isRequired,
  answers: T.object
}

const LastQuestionButton = props =>
  (props.showFeedback) ?
    (!props.feedbackEnabled) ?
      (props.currentStepSend) ?
        <ValidateButton onClick={() => props.openFeedbackAndValidate(props.step)} resourceId={props.resourceId} /> :
        <NextButton onClick={() => props.openFeedbackAndValidate(props.step)} resourceId={props.resourceId} /> :
      <FinishButton onClick={props.finish} resourceId={props.resourceId} /> :
    <FinishButton onClick={props.finish} resourceId={props.resourceId} />

LastQuestionButton.propTypes = {
  step: T.object.isRequired,
  finish: T.func.isRequired,
  resourceId: T.string.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired
}

const NotLastQuestionButton = props =>
  (props.currentStepSend) ?
    (props.showFeedback) ?
      (!props.feedbackEnabled) ?
        <ValidateButton onClick={() => props.openFeedbackAndValidate(props.step)} resourceId={props.resourceId} /> :
        <NextButton onClick={() => props.navigateToAndValidate(props.next)} resourceId={props.resourceId} /> :
      <ValidateButton onClick={() => props.navigateToAndValidate(props.next)} resourceId={props.resourceId} /> :
    <NextButton onClick={() => props.navigateToAndValidate(props.next)} resourceId={props.resourceId} />

NotLastQuestionButton.propTypes = {
  step: T.object.isRequired,
  next: T.object,
  resourceId: T.string.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  navigateToAndValidate: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired
}

const PlayerNav = props =>
  <nav className="player-nav component-container">
    <div className="backward">
      {props.showBack && props.previous &&
        <PreviousButton onClick={() => props.navigateToAndValidate(props.previous)} resourceId={props.resourceId} />
      }
    </div>

    <div className="forward">
      <ForwardButton
        resourceId={props.resourceId}
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
  showBack: T.bool,
  step: T.object.isRequired,
  navigateTo: T.func.isRequired,
  finish: T.func.isRequired,
  resourceId: T.string.isRequired,
  navigateToAndValidate: T.func.isRequired,
  openFeedbackAndValidate: T.func.isRequired,
  submit: T.func.isRequired,
  showFeedback: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,
  answers: T.object.isRequired,
  mandatoryQuestions: T.bool.isRequired
}

PlayerNav.defaultProps = {
  previous: null,
  next: null
}

export {
  PlayerNav
}
