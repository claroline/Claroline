import React from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PathPrevious = props => {
  const currentIndex = props.all.findIndex(step => props.current.id === step.id)

  let previous
  if (0 !== currentIndex) {
    previous = props.all[currentIndex - 1]
  }

  if (previous) {
    return (
      <LinkButton
        className="btn btn-link btn-previous"
        size="lg"
        target={`${props.path}/${previous.slug}`}
        onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
      >
        <span className="fa fa-angle-double-left icon-with-text-right" />
        {trans('previous')}
      </LinkButton>
    )
  }
}

PathPrevious.propTypes = {
  path: T.string.isRequired,
  current: T.shape(
    StepTypes.propTypes
  ),
  all: T.arrayOf(T.shape(
    StepTypes.propTypes
  ))
}

PathPrevious.defaultProps = {
  all: []
}

export {
  PathPrevious
}
