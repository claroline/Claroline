import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {Summary} from '#/main/app/content/components/summary'

function getStepSummary(step, path) {
  return {
    type: LINK_BUTTON,
    icon: classes('step-progression fa fa-fw fa-circle', get(step, 'userProgression.status')),
    label: step.title,
    target: `${path}/edit/${step.id}`,
    //additional: this.getStepActions(step), // TODO : restore actions
    children: step.children ? step.children.map(step => getStepSummary(step, path)) : []
  }
}

const EditorMenu = props =>
  <Summary
    links={[{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-cog',
      label: trans('parameters'),
      target: `${props.path}/edit/parameters`
    }].concat(props.steps.map(step => getStepSummary(step, props.path)), [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('step_add', {}, 'path'),
      callback: props.addStep
    }])}
  />

EditorMenu.propTypes = {
  path: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // TODO : step types
  })),
  addStep: T.func.isRequired
}

EditorMenu.defaultProps = {
  steps: []
}

export {
  EditorMenu
}
