import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Timer} from '#/main/core/layout/gauge/components/timer'
import {ContentLoader} from '#/main/app/content/components/loader'
import {Alert} from '#/main/app/components/alert'
import {PageContent} from '#/main/app/page'
import {ProgressBar} from '#/main/app/components/progress-bar'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {ResourcePage} from '#/main/core/resource'
import {DragDropProvider} from '#/main/app/overlays/dnd/components/provider'

import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'
import {PlayerNav} from '#/plugin/exo/resources/quiz/player/components/nav-bar'
import {PlayerRestrictions} from '#/plugin/exo/resources/quiz/player/components/restrictions'
import {PlayerStep} from '#/plugin/exo/resources/quiz/player/components/step'

class QuizPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fetching: true,
      error: false
    }

    this.navigate = this.navigate.bind(this)
  }

  componentDidMount() {
    this.props.start().then(
      () => this.setState({fetching: false}),
      (error) => this.setState({fetching: false, error: error})
    )
  }

  navigate(path) {
    this.props.history.push(this.props.path + '/' + path)
  }

  render() {
    return (
      <ResourcePage>
        <PageContent>
          <div className="quiz-player content-lg">
            {this.props.progression &&
              <ProgressBar
                className="progress-minimal"
                value={Math.floor((this.props.progression.current / this.props.progression.total) * 100)}
                size="xs"
                variant="learning"
              />
            }

            {(this.props.progression || this.props.isTimed) &&
              <div className="quiz-gauges-container">
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
              </div>
            }

            {this.props.testMode &&
              <Alert
                type="info"
                icon="fa fa-fw fa-flask"
                title={trans('test_mode', {}, 'quiz')} className="alert-test-mode"
              >
                {trans('test_mode_desc', {}, 'quiz')}
              </Alert>
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
              <PlayerStep
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
          <DragDropProvider>
            <CustomDragLayer key="drag-layer" />
          </DragDropProvider>
        </PageContent>
      </ResourcePage>
    )
  }
}

QuizPlayer.propTypes = {
  path: T.string,
  resourceId: T.string.isRequired,
  workspace: T.object,
  history: T.object.isRequired,
  quizId: T.string.isRequired,
  testMode: T.bool.isRequired,
  numbering: T.string,
  questionNumbering: T.string,
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

QuizPlayer.defaultProps = {
  next: null,
  previous: null,
  answers: {}
}

export {
  QuizPlayer
}
