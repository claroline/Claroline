import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {LINK_BUTTON} from '#/main/app/buttons'
import {Summary} from '#/main/app/content/components/summary'

function getStepSummary(step, path) {
  return {
    type: LINK_BUTTON,
    icon: classes('step-progression fa fa-fw fa-circle', get(step, 'userProgression.status')),
    label: step.title,
    target: `${path}/play/${step.id}`,
    //additional: this.getStepActions(step), // TODO : restore actions
    children: step.children ? step.children.map(step => getStepSummary(step, path)) : []
  }
}

const PlayerMenu = props =>
  <Summary
    links={props.steps.map(step => getStepSummary(step, props.path))}
  />

PlayerMenu.propTypes = {
  path: T.string.isRequired,
  steps: T.arrayOf(T.shape({
    // TODO : step types
  }))
}

PlayerMenu.defaultProps = {
  steps: []
}

export {
  PlayerMenu
}
