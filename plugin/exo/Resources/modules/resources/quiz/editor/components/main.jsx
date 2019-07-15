import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {Routes} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Form} from '#/main/app/content/form/components/form'

import {Step as StepTypes} from '#/plugin/exo/resources/quiz/prop-types'
import {EditorParameters} from '#/plugin/exo/resources/quiz/editor/components/parameters'
import {EditorStep} from '#/plugin/exo/resources/quiz/editor/components/step'
import {EditorSummary} from '#/plugin/exo/resources/quiz/editor/components/summary'
import {MODAL_STEP_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/step-position'

class EditorMain extends Component {
  getStepActions(step, index) {
    return [
      {
        name: 'copy',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-clone',
        label: trans('copy', {}, 'actions'),
        modal: [MODAL_STEP_POSITION, {
          icon: 'fa fa-fw fa-clone',
          title: trans('copy'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: index + 1}, 'quiz')
          },
          steps: this.props.steps.map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz')
          })),
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('copy', {}, 'actions'),
            callback: () => this.props.copyStep(step.id, position)
          })
        }],
        group: trans('management')
      }, {
        name: 'move',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-arrows',
        label: trans('move', {}, 'actions'),
        modal: [MODAL_STEP_POSITION, {
          icon: 'fa fa-fw fa-arrows',
          title: trans('movement'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: index + 1}, 'quiz')
          },
          steps: this.props.steps.map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz')
          })),
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('move', {}, 'actions'),
            callback: () => this.props.moveStep(step.id, position)
          })
        }],
        group: trans('management')
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete', {}, 'actions'),
        callback: () => this.props.removeStep(step.id),
        confirm: {
          title: trans('deletion'),
          subtitle: step.title || trans('step', {number: index + 1}, 'quiz'),
          message: trans('remove_step_confirm_message', {}, 'quiz')
        },
        dangerous: true,
        group: trans('management')
      }
    ]
  }

  /*getItemActions(step, item, index) {
    return [

    ]
  }*/

  render() {
    return (
      <Form
        className="quiz-editor"
        validating={this.props.validating}
        pendingChanges={this.props.pendingChanges}
        errors={!isEmpty(this.props.errors)}
        save={{
          type: CALLBACK_BUTTON,
          callback: () => this.props.save(this.props.quizId)
        }}
        cancel={{
          type: LINK_BUTTON,
          target: this.props.path,
          exact: true
        }}
      >
        <EditorSummary
          validating={this.props.validating}
          errors={this.props.errors}
          steps={this.props.steps.map((step, stepIndex) => ({
            id: step.id,
            title: step.title,
            actions: this.getStepActions(step, stepIndex)
          }))}
          add={this.props.addStep}
          path={this.props.path}
        />

        <div className="edit-zone user-select-disabled">
          <Routes
            path={this.props.path}
            routes={[
              {
                path: '/edit/parameters',
                render: () => (
                  <EditorParameters
                    formName={this.props.formName}
                    quizType={this.props.quizType}
                    score={this.props.score}
                    numberingType={this.props.numberingType}
                    randomPick={this.props.randomPick}
                    tags={this.props.tags}
                    workspace={this.props.workspace}
                    steps={this.props.steps}
                    update={this.props.update}
                  />
                )
              }, {
                path: '/edit/:id',
                render: (routeProps) => {
                  const stepIndex = this.props.steps.findIndex(step => routeProps.match.params.id === step.id)
                  if (-1 !== stepIndex) {
                    const currentStep = this.props.steps[stepIndex]

                    return (
                      <EditorStep
                        formName={this.props.formName}
                        path={`steps[${stepIndex}]`}
                        numberingType={this.props.numberingType}
                        steps={this.props.steps}
                        index={stepIndex}
                        id={currentStep.id}
                        title={currentStep.title}
                        hasExpectedAnswers={this.props.hasExpectedAnswers}
                        score={this.props.score}
                        items={currentStep.items}
                        errors={get(this.props.errors, `steps[${stepIndex}]`)}
                        actions={this.getStepActions(currentStep, stepIndex)}
                        update={(prop, value) => this.props.update(`steps[${stepIndex}].${prop}`, value)}
                        moveItem={(itemId, position) => this.props.moveItem(itemId, position)}
                        copyItem={(itemId, position) => this.props.copyItem(itemId, position)}
                      />
                    )
                  }

                  // routeProps.history.push(`${this.props.path}/edit`)

                  return null
                }
              }
            ]}

            redirect={[
              {from: '/edit', exact: true, to: '/edit/parameters'}
            ]}
          />
        </div>
      </Form>
    )
  }
}

EditorMain.propTypes = {
  path: T.string.isRequired,
  formName: T.string.isRequired,
  validating: T.bool.isRequired,
  pendingChanges: T.bool.isRequired,
  errors: T.object,

  quizId: T.string.isRequired,
  quizType: T.string.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  score: T.shape({
    type: T.string.isRequired
  }).isRequired,
  workspace: T.object,
  numberingType: T.string,
  tags: T.array.isRequired,
  randomPick: T.string,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),

  update: T.func.isRequired,
  save: T.func.isRequired,
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired,
  moveItem: T.func.isRequired,
  copyItem: T.func.isRequired

}

export {
  EditorMain
}
