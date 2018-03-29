import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Link} from '#/main/core/layout/button/components/link.jsx'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const PathNavigation = props =>
  <nav className="path-navigation">
    <Link
      className="btn-lg btn-link"
      disabled={!props.previous}
      target={props.previous ? `#${props.prefix}/${props.previous.id}`:''}
    >
      <span className="fa fa-angle-double-left icon-with-text-right" />
      {trans('previous')}
    </Link>

    <Link
      className="btn-lg btn-link"
      disabled={!props.next}
      target={props.next ? `#${props.prefix}/${props.next.id}`:''}
    >
      {trans('next')}
      <span className="fa fa-angle-double-right icon-with-text-left" />
    </Link>
  </nav>

PathNavigation.propTypes = {
  prefix: T.string.isRequired,
  previous: T.shape(
    StepTypes.propTypes
  ),
  next: T.shape(
    StepTypes.propTypes
  )
}

PathNavigation.defaultProps = {
  previous: null,
  next: null
}

export {
  PathNavigation
}
