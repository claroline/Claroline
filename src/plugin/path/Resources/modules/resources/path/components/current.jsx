import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {PathNav} from '#/plugin/path/resources/path/components/nav'
import {scrollTo} from '#/main/app/dom/scroll'

const PathCurrent = props => {
  const currentIndex = props.all.findIndex(step => props.current.id === step.id)

  return (
    <>
      <ProgressBar
        className="progress-minimal"
        value={Math.floor(((currentIndex+1) / (props.all.length)) * 100)}
        size="xs"
        type="learning"
      />

      {props.children}

      {props.navigation &&
        <PathNav
          path={props.prefix}
          current={props.current}
          all={props.all}
          endPage={props.endPage}
          onNavigate={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
        />
      }
    </>
  )
}

PathCurrent.propTypes = {
  resourceId: T.string.isRequired,
  prefix: T.string.isRequired,
  navigation: T.bool.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  endPage: T.bool,
  // the current step content
  children: T.node
}

PathCurrent.defaultProps = {
  all: [],
  endPage: false
}

export {
  PathCurrent
}
