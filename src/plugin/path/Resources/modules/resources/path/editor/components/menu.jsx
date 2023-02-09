import React from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {MODAL_STEP_POSITION} from '#/plugin/path/resources/path/editor/modals/position'

const EditorMenu = props => {
  function getStepSummary(step) {
    return {
      type: LINK_BUTTON,
      label: step.title,
      target: `${props.basePath}/edit/${step.slug}`,
      active: !!matchPath(props.location.pathname, {path: `${props.basePath}/edit/${step.slug}`}),
      onClick: props.autoClose,
      additional: [
        {
          name: 'add',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('step_add_child', {}, 'path'),
          callback: () => {
            const newSlug = props.addStep(props.path.steps, step)
            props.history.push(`${props.basePath}/edit/${newSlug}`)
          },
          onClick: props.autoClose,
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
            steps: props.steps,
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('copy', {}, 'actions'),
              callback: () => props.copyStep(step.id, position)
            })
          }],
          onClick: props.autoClose,
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
            steps: props.steps,
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('move', {}, 'actions'),
              callback: () => props.moveStep(step.id, position)
            })
          }],
          onClick: props.autoClose,
          group: trans('management')
        }, {
          name: 'delete',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          callback: () => {
            props.removeStep(step.id)
            if (`${props.basePath}/edit/${step.slug}` === props.location.pathname) {
              props.history.push(`${props.basePath}/edit`)
            }
          },
          confirm: {
            title: trans('deletion'),
            subtitle: step.title,
            message: trans('step_delete_confirm', {}, 'path')
          },
          dangerous: true,
          onClick: props.autoClose,
          group: trans('management')
        }
      ],
      children: step.children ? step.children.map(getStepSummary) : []
    }
  }

  return (
    <ContentSummary
      links={[{
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: `${props.basePath}/edit/parameters`,
        onClick: (e) => {
          props.autoClose(e)
          scrollTo('.main-page-content')
        }
      }].concat(props.path.steps.map(getStepSummary), [{
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('step_add', {}, 'path'),
        callback: () => {
          const newSlug = props.addStep(props.path.steps)
          props.history.push(`${props.basePath}/edit/${newSlug}`)
        },
        onClick: (e) => {
          props.autoClose(e)
          scrollTo('.main-page-content')
        }
      }])}
    />
  )
}

EditorMenu.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  basePath: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ),
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired,
  autoClose: T.func.isRequired
}

EditorMenu.defaultProps = {
  steps: []
}

export {
  EditorMenu
}
