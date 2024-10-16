import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PathNav = props => {
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
    <nav className={classes('path-navigation mt-auto', props.className)}>
      {previous &&
        <LinkButton
          className="btn btn-link text-reset btn-previous"
          size="lg"
          target={`${props.path}/${previous.slug}`}
          onClick={props.onNavigate}
        >
          <span className="fa fa-angle-double-left icon-with-text-right" />
          {trans('previous')}
        </LinkButton>
      }

      {next &&
        <LinkButton
          className="btn btn-link btn-next"
          size="lg"
          target={`${props.path}/${next.slug}`}
          onClick={props.onNavigate}
        >
          {trans('next')}
          <span className="fa fa-angle-double-right icon-with-text-left" />
        </LinkButton>
      }

      {!next && props.endPage &&
        <LinkButton
          className="btn btn-link btn-next"
          size="lg"
          target={`${props.path}/end`}
          onClick={props.onNavigate}
        >
          {trans('end')}
          <span className="fa fa-angle-double-right icon-with-text-left" />
        </LinkButton>
      }
    </nav>
  )
}

PathNav.propTypes = {
  className: T.sting,
  path: T.string.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  endPage: T.bool,
  onNavigate: T.func
}

PathNav.defaultProps = {
  all: [],
  endPage: false
}

export {
  PathNav
}
