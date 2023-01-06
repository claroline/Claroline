import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import Panel from 'react-bootstrap/lib/Panel'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {ContentHtml} from '#/main/app/content/components/html'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Timer} from '#/main/core/layout/gauge/components/timer'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ProgressBar} from '#/main/app/content/components/progress-bar'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {getDefinition, isQuestionType} from '#/plugin/exo/items/item-types'
import {getContentDefinition} from '#/plugin/exo/contents/utils'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {select} from '#/plugin/exo/quiz/player/selectors'
import {actions} from '#/plugin/exo/quiz/player/actions'
import {ItemPlayer} from '#/plugin/exo/items/components/item-player'
import {ItemFeedback} from '#/plugin/exo/items/components/item-feedback'
import {ContentItemPlayer} from '#/plugin/exo/contents/components/content-item-player'
import {PlayerNav} from '#/plugin/exo/quiz/player/components/nav-bar'
import {PlayerRestrictions} from '#/plugin/exo/quiz/player/components/restrictions'

// TODO : rethink the loading paper process (it's a little hacky to make it quickly compatible with Router)

const CurrentStep = props => {
  const numbering = getNumbering(props.numbering, props.number - 1)

  return (
    <section className="current-step">
      {props.showTitle &&
        <h3 className="h2 step-title">
          {numbering &&
            <span className="h-numbering">{numbering}</span>
          }

          {props.step.title || trans('step', {number: props.number}, 'quiz')}
        </h3>
      }

      {props.step.description &&
        <ContentHtml className="step-description">{props.step.description}</ContentHtml>
      }

      {props.items.map((item, index) => (
        <Panel key={item.id}>
          {!isQuestionType(item.type) ?
            <ContentItemPlayer
              showTitle={props.showQuestionTitles}
              item={item}
            >
              {React.createElement(getContentDefinition(item.type).player, {item: item})}
            </ContentItemPlayer>
            : (!props.feedbackEnabled ?
              <ItemPlayer
                item={item}
                showHint={props.showHint}
                usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
                showTitle={props.showQuestionTitles}
                numbering={getNumbering(props.questionNumbering, props.number - 1, index)}
              >
                {React.createElement(getDefinition(item.type).player, {
                  item: item,
                  answer: props.answers[item.id] && props.answers[item.id].data ? props.answers[item.id].data : undefined,
                  disabled: !props.answersEditable && props.answers[item.id] && 0 < props.answers[item.id].tries,
                  onChange: (answerData) => props.updateAnswer(item.id, answerData)
                })}
              </ItemPlayer>
              :
              <ItemFeedback
                item={item}
                usedHints={props.answers[item.id] ? props.answers[item.id].usedHints : []}
                showTitle={props.showQuestionTitles}
                numbering={props.questionNumbering !== constants.NUMBERING_NONE ? props.number + '.' + getNumbering(props.questionNumbering, index): null}
              >
                {React.createElement(getDefinition(item.type).feedback, {
                  item: item,
                  answer: props.answers[item.id] && props.answers[item.id].data ? props.answers[item.id].data : undefined
                })}
              </ItemFeedback>
            )}
        </Panel>
      ))}
    </section>
  )
}

CurrentStep.propTypes = {
  numbering: T.string.isRequired,
  questionNumbering: T.string.isRequired,
  number: T.number.isRequired,
  showTitle: T.bool,
  showQuestionTitles: T.bool,
  step: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  items: T.array.isRequired,
  answers: T.object.isRequired,
  feedbackEnabled: T.bool.isRequired,
  answersEditable: T.bool.isRequired,

  updateAnswer: T.func.isRequired,
  showHint: T.func.isRequired
}

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fetching: true,
      error: false
    }

    this.navigate = this.navigate.bind(this)
  }

  componentDidMount() {
    // TODO : display why the user cannot play quiz
    this.props
      .start()
      .then(
        () => this.setState({fetching: false}),
        (error) => this.setState({fetching: false, error: error})
      )
  }

  navigate(path) {
    this.props.history.push(this.props.path + '/' + path)
  }

  // TODO : better error display
  render() {
    return (
      <div className="quiz-player">
        {this.props.progression &&
          <ProgressBar
            className="progress-minimal"
            value={Math.floor((this.props.progression.current / this.props.progression.total) * 100)}
            size="xs"
            type="user"
          />
        }
        {this.props.testMode &&
          <AlertBlock
            type="info"
            icon="fa fa-fw fa-flask"
            title={trans('test_mode', {}, 'quiz')} className="alert-test-mode"
          >
            {trans('test_mode_desc', {}, 'quiz')}
          </AlertBlock>
        }

        {(this.props.progression || this.props.isTimed) &&
          <div className="quiz-gauges-container">
            {this.props.progression &&
              <div className="quiz-progression-container">
                <ScoreGauge
                  type="user"
                  value={this.props.progression.current}
                  total={this.props.progression.total}
                  width={70}
                  height={70}
                />
              </div>
            }

            {this.props.isTimed && this.props.duration > 0 && this.props.paper.startDate &&
              <div className="timer-container">
                <Timer
                  type="user"
                  totalTime={this.props.duration}
                  startDate={this.props.paper.startDate}
                  onTimeOver={() => {
                    this.props.finish(this.props.quizId, this.props.paper, this.props.answers, this.props.showFeedback, false, this.navigate)
                    this.props.showTimeOverMessage()
                  }}
                  width={70}
                  height={70}
                />
              </div>
            }
          </div>
        }

        {this.state.fetching &&
          <ContentLoader />
        }

        {(!this.state.fetching && this.state.error) &&
          <PlayerRestrictions
            {...this.state.error}
            path={this.props.path}
            workspace={this.props.workspace}
            showStatistics={this.props.showStatistics}
          />
        }

        {(!this.state.fetching && !this.state.error) &&
          <CurrentStep
            numbering={this.props.numbering}
            questionNumbering={this.props.questionNumbering}
            number={this.props.number}
            showTitle={this.props.showTitles}
            showQuestionTitles={this.props.showQuestionTitles}
            step={this.props.step}
            items={this.props.items}
            answers={this.props.answers}
            feedbackEnabled={this.props.feedbackEnabled}
            answersEditable={this.props.answersEditable}
            updateAnswer={this.props.updateAnswer}
            showHint={(questionId, hint) => this.props.showHint(this.props.quizId, this.props.paper.id, questionId, hint)}
          />
        }

        {(!this.state.fetching && !this.state.error) &&
          <PlayerNav
            resourceId={this.props.resourceId}
            previous={this.props.previous}
            mandatoryQuestions={this.props.mandatoryQuestions}
            next={this.props.next}
            step={this.props.step}
            answers={this.props.answers}
            showBack={this.props.showBack}
            showFeedback={this.props.showFeedback}
            feedbackEnabled={this.props.feedbackEnabled}
            navigateTo={(step) => this.props.navigateTo(this.props.quizId, this.props.paper.id, step, this.props.answers, false, false)}
            navigateToAndValidate={(step) => {
              const confirm = !this.props.answersEditable && 0 < Object.values(this.props.answers).filter(a => 0 === a.tries).length
              this.props.navigateTo(this.props.quizId, this.props.paper.id, step, this.props.answers, this.props.currentStepSend, false, confirm)}
            }
            openFeedbackAndValidate={(step) => {
              const confirm = !this.props.answersEditable && 0 < Object.values(this.props.answers).filter(a => 0 === a.tries).length
              this.props.navigateTo(this.props.quizId, this.props.paper.id, step, this.props.answers, this.props.currentStepSend, true, confirm)
            }}
            submit={() => this.props.submit(this.props.quizId, this.props.paper.id, this.props.answers)}
            finish={() => this.props.finish(this.props.quizId, this.props.paper, this.props.answers, this.props.showFeedback, this.props.showEndConfirm, this.navigate)}
            currentStepSend={this.props.currentStepSend}
          />
        }
      </div>
    )
  }
}

PlayerComponent.propTypes = {
  path: T.string,
  resourceId: T.string.isRequired,
  workspace: T.object,
  history: T.object.isRequired,
  quizId: T.string.isRequired,
  testMode: T.bool.isRequired,
  numbering: T.string.isRequired,
  questionNumbering: T.string.isRequired,
  showTitles: T.bool,
  showQuestionTitles: T.bool,
  number: T.number.isRequired,
  isTimed: T.bool.isRequired,
  duration: T.number,
  progression: T.shape({
    current: T.number.isRequired,
    total: T.number.isRequired
  }),
  step: T.object,
  items: T.array.isRequired,
  mandatoryQuestions: T.bool.isRequired,
  answers: T.object.isRequired,
  paper: T.shape({
    id: T.string.isRequired,
    number: T.number.isRequired,
    startDate: T.string.isRequired,
    structure: T.object.isRequired
  }).isRequired,
  next: T.object,
  previous: T.object,
  showBack: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  showFeedback: T.bool.isRequired,
  showEndConfirm: T.bool.isRequired,
  feedbackEnabled: T.bool.isRequired,
  currentStepSend: T.bool.isRequired,
  answersEditable: T.bool.isRequired,

  start: T.func.isRequired,
  updateAnswer: T.func.isRequired,
  navigateTo: T.func.isRequired,
  submit: T.func.isRequired,
  finish: T.func.isRequired,
  showHint: T.func.isRequired,
  showTimeOverMessage: T.func.isRequired
}

PlayerComponent.defaultProps = {
  next: null,
  previous: null,
  answers: {}
}

const Player = withRouter(connect(
  state => {
    const paper = select.paper(state)
    return {
      // general info
      path: resourceSelect.path(state),
      workspace: resourceSelect.workspace(state),
      resourceId: resourceSelect.id(state),
      quizId: select.quizId(state),

      // general attempt info
      testMode: select.testMode(state),
      paper: paper,
      progression: select.progressionDisplayed(state) ? {
        current: Object.values(select.answers(state)).filter(a => a.data && a.data.length > 0).length,
        total: select.countItems(state)
      } : undefined,

      // attempt parameters
      mandatoryQuestions: select.mandatoryQuestions(state),
      numbering: select.quizNumbering(state),
      questionNumbering: select.questionNumbering(state),
      showTitles: select.showTitles(state),
      showQuestionTitles: select.showQuestionTitles(state),
      isTimed: select.isTimed(state),
      duration: select.duration(state),
      answersEditable: select.answersEditable(state),
      showStatistics: select.showStatistics(state),
      showBack: select.showBack(state),
      showFeedback: select.showFeedback(state),
      showEndConfirm: select.showEndConfirm(state),
      feedbackEnabled: select.feedbackEnabled(state),

      // current step info
      number: select.currentStepNumber(state),
      step: select.currentStep(state),
      items: select.currentStepItems(state),
      answers: select.currentStepAnswers(state),
      currentStepSend: select.currentStepSend(state),

      next: select.next(state),
      previous: select.previous(state)
    }
  },
  dispatch => ({
    start() {
      // The return is to be able to link on the Promise (this is not really clean)
      return dispatch(actions.play())
    },
    updateAnswer(questionId, answerData) {
      dispatch(actions.updateAnswer(questionId, answerData))
    },
    navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback, confirm = false) {
      if (confirm) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          title: trans('validate_step_title', {}, 'quiz'),
          question: trans('validate_step_question', {}, 'quiz'),
          handleConfirm: () => dispatch(actions.navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback))
        }))
      } else {
        dispatch(actions.navigateTo(quizId, paperId, nextStep, pendingAnswers, currentStepSend, openFeedback))
      }
    },
    submit(quizId, paperId, answers) {
      dispatch(actions.submit(quizId, paperId, answers))
    },
    finish(quizId, paper, pendingAnswers, showFeedback, showConfirm, navigate) {
      if (showConfirm) {
        dispatch(modalActions.showModal(MODAL_CONFIRM, {
          title: trans('finish_confirm_title', {}, 'quiz'),
          question: trans('finish_confirm_question', {}, 'quiz'),
          handleConfirm: () => dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback, navigate))
        }))
      } else {
        dispatch(actions.finish(quizId, paper, pendingAnswers, showFeedback, navigate))
      }
    },
    showHint(quizId, paperId, questionId, hint) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('hint_confirm_title', {}, 'quiz'),
        question: trans('hint_confirm_question', {}, 'quiz'),
        handleConfirm: () => dispatch(actions.showHint(quizId, paperId, questionId, hint))
      }))
    },
    showTimeOverMessage() {
      dispatch(modalActions.showModal(MODAL_ALERT, {
        title: trans('time_over', {}, 'quiz'),
        message: trans('time_over_message', {}, 'quiz'),
        type: 'info'
      }))
    }
  })
)(PlayerComponent))

export {
  Player
}
