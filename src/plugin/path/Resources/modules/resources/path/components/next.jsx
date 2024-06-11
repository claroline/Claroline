import React from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PathNext = props => {
  const currentIndex = props.all.findIndex(step => props.current.id === step.id)

  let previous
  if (0 !== currentIndex) {
    previous = props.all[currentIndex - 1]
  }

  let next
  if (props.all.length > currentIndex + 1) {
    next = props.all[currentIndex + 1]
  }

  if (next) {
    return (
      <LinkButton
        className="btn btn-link btn-next"
        primary={true}
        size="lg"
        target={`${props.path}/${next.slug}`}
        onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
      >
        {trans('next')}
        <span className="fa fa-angle-double-right icon-with-text-left" />
      </LinkButton>
    )
  }

  if (!next && props.endPage) {
    return (
      <LinkButton
        className="btn btn-link btn-next"
        primary={true}
        size="lg"
        target={`${props.path}/end`}
        onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
      >
        {trans('end')}
        <span className="fa fa-angle-double-right icon-with-text-left" />
      </LinkButton>
    )
  }
}

PathNext.propTypes = {
  path: T.string.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  endPage: T.bool
}

PathNext.defaultProps = {
  all: [],
  endPage: false
}

export {
  PathNext
}
