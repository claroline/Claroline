import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Lesson as LessonTypes} from '#/plugin/lesson/resources/lesson/prop-types'

const LessonCurrent = props => {
  const currentIndex = props.all.findIndex(chapter => props.current.id === chapter.id)

  return (
    <Fragment>
      <ProgressBar
        className="progress-minimal"
        value={Math.floor(((currentIndex+1) / props.all.length) * 100)}
        size="xs"
        type="user"
      />

      {props.children}

      <nav className="lesson-navigation">
        {props.current.previousSlug &&
          <LinkButton
            className="btn-link btn-previous"
            size="lg"
            target={`${props.prefix}/${props.current.previousSlug}`}
          >
            <span className="fa fa-angle-double-left icon-with-text-right" />
            {trans('previous')}
          </LinkButton>
        }

        {props.current.nextSlug &&
          <LinkButton
            className="btn-link btn-next"
            primary={true}
            size="lg"
            target={`${props.prefix}/${props.current.nextSlug}`}
          >
            {trans('next')}
            <span className="fa fa-angle-double-right icon-with-text-left" />
          </LinkButton>
        }
      </nav>
    </Fragment>
  )
}

LessonCurrent.propTypes = {
  prefix: T.string.isRequired,
  current: T.shape(
    LessonTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    LessonTypes.propTypes
  )),
  // the current step content
  children: T.node
}

LessonCurrent.defaultProps = {
  all: []
}

export {
  LessonCurrent
}
