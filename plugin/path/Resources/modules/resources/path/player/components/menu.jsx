import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Summary} from '#/main/app/content/components/summary'

const PlayerMenu = props => {
  function getStepSummary(step) {
    return {
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', get(step, 'userProgression.status')),
      label: step.title,
      target: `${props.path}/play/${step.id}`,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/play/${step.id}`}),
      children: step.children ? step.children.map(getStepSummary) : []
    }
  }

  return (
    <Summary
      links={props.steps.map(getStepSummary)}
    />
  )
}

PlayerMenu.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }),
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
