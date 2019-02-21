import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {SummarizedContent} from '#/main/app/content/summary/components/content'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

import {PathCurrent} from '#/plugin/path/resources/path/components/current'
import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {constants} from '#/plugin/path/resources/path/constants'
import {getNumbering} from '#/plugin/path/resources/path/utils'

import {EditorParameters} from '#/plugin/path/resources/path/editor/components/parameters'
import {EditorStep} from '#/plugin/path/resources/path/editor/components/step'
import {getFormDataPart} from '#/plugin/path/resources/path/editor/utils'
import {MODAL_STEP_POSITION} from '#/plugin/path/resources/path/editor/modals/position'

class EditorMain extends Component {
  constructor(props) {
    super(props)

    this.getStepActions = this.getStepActions.bind(this)
    this.getStepSummary = this.getStepSummary.bind(this)
  }

  getStepActions(step) {
    return [
      {
        name: 'add',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('step_add_child', {}, 'path'),
        callback: () => this.props.addStep(step.id),
        group: trans('management')
      }, {
        name: 'copy',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-clone',
        label: trans('copy', {}, 'actions'),
        modal: [MODAL_STEP_POSITION, {
          icon: 'fa fa-fw fa-clone',
          title: trans('copy'),
          step: step,
          steps: this.props.steps,
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
          step: step,
          steps: this.props.steps,
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
        callback: () => this.props.removeStep(step.id, this.props.history),
        confirm: {
          title: trans('deletion'),
          subtitle: step.title,
          message: trans('step_delete_confirm', {}, 'path')
        },
        dangerous: true,
        group: trans('management')
      }
    ]
  }

  getStepSummary(step) {
    return {
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-circle', step.userProgression && step.userProgression.status),
      label: step.title,
      target: `/edit/${step.id}`,
      additional: this.getStepActions(step),
      children: step.children ? step.children.map(this.getStepSummary) : []
    }
  }

  render() {
    return (
      <Fragment>
        <h2 className="sr-only">{trans('configuration')}</h2>
        <SummarizedContent
          summary={{
            displayed: true,
            opened: this.props.summaryOpened,
            pinned: this.props.summaryPinned,
            links: [{
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-cog',
              label: trans('parameters'),
              target: '/edit/parameters'
            }].concat(this.props.path.steps.map(this.getStepSummary), [{
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('step_add', {}, 'path'),
              callback: () => this.props.addStep()
            }])
          }}
        >
          <Routes
            redirect={[
              {from: '/edit/', to: '/edit/parameters', exact: true}
            ]}
            routes={[
              {
                path: '/edit/parameters',
                exact: true,
                render: () => {
                  const Parameters = (
                    <EditorParameters
                      path={this.props.path}
                      workspace={this.props.workspace}
                    />
                  )

                  return Parameters
                }
              }, {
                path: '/edit/:id',
                render: (routeProps) => {
                  const step = this.props.steps.find(step => routeProps.match.params.id === step.id)

                  const CurrentStep = (
                    <PathCurrent
                      prefix="/edit"
                      current={step}
                      all={this.props.steps}
                      navigation={true}
                    >
                      <EditorStep
                        {...step}
                        pathId={this.props.path.id}
                        workspace={this.props.workspace}
                        resourceParent={this.props.resourceParent}
                        actions={this.getStepActions(step)}
                        numbering={getNumbering(this.props.path.display.numbering, this.props.path.steps, step)}
                        customNumbering={constants.NUMBERING_CUSTOM === this.props.path.display.numbering}
                        stepPath={getFormDataPart(step.id, this.props.path.steps)}
                        onEmbeddedResourceClose={this.props.computeResourceDuration}
                      />
                    </PathCurrent>
                  )

                  return CurrentStep
                }
              }
            ]}
          />
        </SummarizedContent>
      </Fragment>
    )
  }
}

EditorMain.propTypes = {
  workspace: T.object,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  resourceParent: T.shape(
    ResourceNodeTypes.propTypes
  ),

  // summary
  summaryOpened: T.bool.isRequired,
  summaryPinned: T.bool.isRequired,

  // step actions
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired,

  // resource management
  computeResourceDuration: T.func.isRequired,
  history: T.shape({
    location: T.shape({
      pathname: T.string.isRequired
    }).isRequired,
    push: T.func.isRequired
  }).isRequired
}

export {
  EditorMain
}
