import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {LinkButton} from '#/main/app/button/components/link'
import {ProgressBar} from '#/main/core/layout/components/progress-bar'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

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

      {props.navigation &&
        <nav className="path-navigation">
          {previous &&
            <LinkButton
              className="btn-link btn-previous"
              disabled={!previous}
              primary={true}
              size="lg"
              target={previous ? `${props.prefix}/${previous.id}`:''}
            >
              <span className="fa fa-angle-double-left icon-with-text-right" />
              {trans('previous')}
            </LinkButton>
          }

          {next &&
            <LinkButton
              className="btn-link btn-next"
              disabled={!next}
              primary={true}
              size="lg"
              target={next ? `${props.prefix}/${next.id}`:''}
            >
              {trans('next')}
              <span className="fa fa-angle-double-right icon-with-text-left" />
            </LinkButton>
          }
        </nav>
      }
    </div>
  )
}

PathCurrent.propTypes = {
  prefix: T.string.isRequired,
  navigation: T.bool.isRequired,
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
