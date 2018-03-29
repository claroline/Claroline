import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ProgressBar} from '#/main/core/layout/components/progress-bar.jsx'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {PathNavigation} from '#/plugin/path/resources/path/components/nav.jsx'

// todo manage unknown steps
const PathCurrent = props => {
  const currentIndex = props.all.findIndex(step => props.current.id === step.id)

  let previous
  if (0 !== currentIndex) {
    previous = props.all[currentIndex - 1]
  }

  let next
  if (props.all.length > currentIndex + 1) {
    next = props.all[currentIndex + 1]
  }

  return (
    <div className="content-container">
      <ProgressBar
        value={Math.floor(((currentIndex+1) / props.all.length) * 100)}
        size="xs"
        type="user"
      />

      {props.children}

      <PathNavigation
        prefix={props.prefix}
        previous={previous}
        next={next}
      />
    </div>
  )
}

PathCurrent.propTypes = {
  prefix: T.string.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  // the current step content
  children: T.node
}

PathCurrent.defaultProps = {
  all: []
}

export {
  PathCurrent
}
