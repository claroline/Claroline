import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_RESOURCE_EXPLORER} from '#/main/core/resource/modals/explorer'
import {Routes, withRouter} from '#/main/app/router'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {actions, selectors} from '#/plugin/path/resources/path/editor/store'
import {actions as pathActions} from '#/plugin/path/resources/path/store'
import {PathCurrent} from '#/plugin/path/resources/path/components/current'
import {PathSummary} from '#/plugin/path/resources/path/components/summary'
import {ParametersForm} from '#/plugin/path/resources/path/editor/components/parameters-form'
import {StepForm} from '#/plugin/path/resources/path/editor/components/step-form'
import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {constants} from '#/plugin/path/resources/path/constants'
import {getNumbering, flattenSteps} from '#/plugin/path/resources/path/utils'
import {getFormDataPart} from '#/plugin/path/resources/path/editor/utils'

// todo : replaces copy/paste feature by a duplicate one (that's how it works elsewhere)

const EditorComponent = props =>
  <section className="summarized-content">
    <h2 className="sr-only">{trans('configuration')}</h2>

    <PathSummary
      prefix="edit"
      steps={props.path.steps}
      actions={(step) => [
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('step_add_child', {}, 'path'),
          callback: () => props.addStep(step)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-files-o',
          label: trans('copy', {}, 'actions'),
          callback: () => props.copyStep(step)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-clipboard',
          label: trans('paste', {}, 'actions'),
          callback: () => props.pasteStep(step),
          displayed: !!props.copy
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          callback: () => props.removeStep(step, props.history)
        }
      ]}
      parameters={true}
      add={props.addStep}
    />

    <Routes
      redirect={[
        {from: '/edit/', to: '/edit/parameters', exact: true}
      ]}
      routes={[
        {
          path: '/edit/parameters',
          exact: true,
          render: () => {
            const Parameters = <ParametersForm path={props.path} workspace={props.workspace} saveForm={() => props.saveForm(props.path.id)}/>

            return Parameters
          }
        }, {
          path: '/edit/:id',
          render: (routeProps) => {
            const step = props.steps.find(step => routeProps.match.params.id === step.id)

            const CurrentStep = (
              <PathCurrent
                prefix="/edit"
                current={step}
                all={props.steps}
                navigation={true}
              >
                <h3 className="h2 step-title">
                  {getNumbering(props.path.display.numbering, props.path.steps, step) &&
                    <span className="h-numbering">{getNumbering(props.path.display.numbering, props.path.steps, step)}</span>
                  }

                  {step.title}
                </h3>

                <StepForm
                  {...step}
                  workspace={props.workspace}
                  resourceParent={props.resourceParent}
                  numbering={getNumbering(props.path.display.numbering, props.path.steps, step)}
                  customNumbering={constants.NUMBERING_CUSTOM === props.path.display.numbering}
                  stepPath={getFormDataPart(step.id, props.path.steps)}
                  pickSecondaryResources={stepId => props.pickResources(stepId, 'secondary', props.resourceParent)}
                  removeSecondaryResource={props.removeSecondaryResource}
                  updateSecondaryResourceInheritance={props.updateSecondaryResourceInheritance}
                  removeInheritedResource={props.removeInheritedResource}
                  saveForm={() => props.saveForm(props.path.id)}
                  onEmbeddedResourceClose={props.computeResourceDuration}
                />
              </PathCurrent>
            )

            return CurrentStep
          }
        }
      ]}
    />
  </section>

EditorComponent.propTypes = {
  workspace: T.object,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  copy: T.shape(StepTypes.propTypes),
  resourceParent: T.shape(ResourceNodeTypes.propTypes),
  addStep: T.func.isRequired,
  removeStep: T.func.isRequired,
  pickResources: T.func.isRequired,
  removeSecondaryResource: T.func.isRequired,
  updateSecondaryResourceInheritance: T.func.isRequired,
  removeInheritedResource: T.func.isRequired,
  copyStep: T.func.isRequired,
  pasteStep: T.func.isRequired,
  saveForm: T.func.isRequired,
  computeResourceDuration: T.func.isRequired,
  history: T.shape({
    location: T.shape({
      pathname: T.string.isRequired
    }).isRequired,
    push: T.func.isRequired
  }).isRequired
}

// todo merge resources pickers

const Editor = withRouter(connect(
  state => ({
    path: selectors.path(state),
    steps: flattenSteps(selectors.steps(state)),
    copy: selectors.stepCopy(state),
    resourceParent: resourceSelect.parent(state),
    workspace: resourceSelect.workspace(state)
  }),
  dispatch => ({
    addStep(parentStep = null) {
      dispatch(actions.addStep(parentStep ? parentStep.id : null))
    },
    removeStep(step, history) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: trans('step_delete_title', {}, 'path'),
          question: trans('step_delete_confirm', {}, 'path'),
          dangerous: true,
          handleConfirm: () => {
            dispatch(actions.removeStep(step.id))

            if (`/edit/${step.id}` === history.location.pathname) {
              history.push('/edit')
            }
          }
        })
      )
    },
    copyStep(step) {
      dispatch(actions.copyStep(step))
    },
    pasteStep(parentStep = null) {
      dispatch(actions.paste(parentStep ? parentStep.id : null))
    },
    pickResources(stepId, usage = 'primary', current = null) {
      let title
      let callback
      if ('secondary' === usage) {
        title = trans('add_secondary_resources', {}, 'path')
        callback = (selected) => dispatch(actions.addSecondaryResources(stepId, selected))
      }
      dispatch(modalActions.showModal(MODAL_RESOURCE_EXPLORER, {
        title: title,
        current: current,
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => callback(selected)
        })
      }))
    },
    removeSecondaryResource(stepId, id) {
      dispatch(actions.removeSecondaryResources(stepId, [id]))
    },
    updateSecondaryResourceInheritance(stepId, id, value) {
      dispatch(actions.updateSecondaryResourceInheritance(stepId, id, value))
    },
    removeInheritedResource(stepId, id) {
      dispatch(actions.removeInheritedResources(stepId, [id]))
    },
    saveForm(pathId) {
      dispatch(formActions.saveForm(selectors.FORM_NAME, ['apiv2_path_update', {id: pathId}]))
    },
    computeResourceDuration(resourceId) {
      dispatch(pathActions.computeResourceDuration(resourceId))
    }
  })
)(EditorComponent))

export {
  Editor
}
