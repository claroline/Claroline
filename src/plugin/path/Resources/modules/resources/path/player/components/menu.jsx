import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentSummary} from '#/main/app/content/components/summary'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PlayerMenu = props => {
  function getStepSummary(step) {
    return {
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', get(props.stepsProgression, step.id, 'unseen')),
      label: step.title,
      target: `${props.path}/play/${step.slug}`,
      active: !!matchPath(props.location.pathname, {path: `${props.path}/play/${step.slug}`}),
      children: step.children ? step.children.map(getStepSummary) : [],
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }
  }

  let baseLinks = []
  if (props.overview) {
    baseLinks = [{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-home',
      label: trans('home'),
      target: props.path,
      exact: true,
      onClick: (e) => {
        props.autoClose(e)
        scrollTo('.main-page-content')
      }
    }]
  }

  return (
    <ContentSummary
      links={baseLinks.concat(
        props.steps.map(getStepSummary)
      )}
    />
  )
}

PlayerMenu.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }),
  path: T.string.isRequired,
  overview: T.bool,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  stepsProgression: T.object,
  autoClose: T.func
}

PlayerMenu.defaultProps = {
  steps: []
}

export {
  PlayerMenu
}
