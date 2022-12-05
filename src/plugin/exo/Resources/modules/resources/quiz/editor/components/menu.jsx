import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {scrollTo} from '#/main/app/dom/scroll'
import {matchPath} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

import {MODAL_STEP_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/step-position'

const EditorMenu = props =>
  <ContentSummary
    links={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('parameters'),
        target: `${props.path}/edit/parameters`,
        subscript: !isEmpty(omit(props.errors, 'steps')) ? {
          type: 'text',
          status: props.validating ? 'danger' : 'warning',
          value: <span className={classes('fa fa-fw', {'fa-warning': props.validating, 'fa-clock': !props.validating})} />
        } : undefined,
        onClick: (e) => {
          props.autoClose(e)
          scrollTo('.main-page-content')
        }
      }
    ].concat(props.steps.map((step, index) => ({
      type: LINK_BUTTON,
      label: step.title || trans('step', {number: index + 1}, 'quiz'),
      target: `${props.path}/edit/${step.slug}`,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/edit/${step.slug}`}),
      subscript: !isEmpty(get(props.errors, `steps[${index}]`)) ? {
        type: 'text',
        status: props.validating ? 'danger' : 'warning',
        value: <span className={classes('fa fa-fw', {'fa-warning': props.validating, 'fa-clock': !props.validating})} />
      } : undefined,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      },
      additional: [
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
            steps: props.steps.map((s, i) => ({
              id: s.id,
              title: s.title || trans('step', {number: i + 1}, 'quiz')
            })),
            selectAction: (position) => ({
              type: CALLBACK_BUTTON,
              label: trans('copy', {}, 'actions'),
              callback: () => props.copyStep(step.id, props.steps, position)
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
            step: {
              id: step.id,
              title: step.title || trans('step', {number: index + 1}, 'quiz')
            },
            steps: props.steps.map((s, i) => ({
              id: s.id,
              title: s.title || trans('step', {number: i + 1}, 'quiz')
            })),
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
            if (`${props.path}/edit/${step.slug}` === props.location.pathname) {
              props.history.push(`${props.path}/edit`)
            }
          },
          confirm: {
            title: trans('deletion'),
            subtitle: step.title || trans('step', {number: index + 1}, 'quiz'),
            message: trans('remove_step_confirm_message', {}, 'quiz')
          },
          dangerous: true,
          onClick: props.autoClose,
          group: trans('management')
        }
      ]
    })), [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('step_add', {}, 'path'),
      callback: () => {
        const newSlug = props.addStep(props.steps)
        props.history.push(`${props.path}/edit/${newSlug}`)
      },
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }, {
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-database',
      label: trans('show_questions_bank', {}, 'actions'),
      target: `${props.path}/edit/bank`,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }])}
  />

EditorMenu.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  path: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // TODO : step types
  })),
  errors: T.object,
  validating: T.bool.isRequired,
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
