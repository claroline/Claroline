import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const Parameters = props =>
  <ul className="evaluation-parameters">
    <li className="evaluation-parameter">
      <span className="fa fa-fw fa-upload icon-with-text-right" aria-hidden={true} />
      {trans('drops_done_by', {}, 'dropzone')}
      &nbsp;
      <b>{constants.DROP_TYPES[props.dropType]}</b>
    </li>
    <li className="evaluation-parameter">
      <span className="fa fa-fw fa-check-square-o icon-with-text-right" />
      {trans('reviews_done_by', {}, 'dropzone')}
      &nbsp;
      <b>{constants.REVIEW_TYPES[props.reviewType]}</b>
    </li>
  </ul>

Parameters.propTypes = {
  dropType: T.oneOf(
    Object.keys(constants.DROP_TYPES)
  ).isRequired,
  reviewType: T.oneOf(
    Object.keys(constants.REVIEW_TYPES)
  ).isRequired
}

export {
  Parameters
}
