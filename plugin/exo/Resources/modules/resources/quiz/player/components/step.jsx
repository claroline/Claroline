import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'

const PlayerStep = props =>
  <div className="current-step">
    <h3>{props.title ? props.title : trans('step', {number: props.number}, 'quiz')}</h3>

    {props.description &&
      <HtmlText className="step-description">{props.description}</HtmlText>
    }

  </div>

PlayerStep.propTypes = {
  number: T.number.isRequired,
  title: T.string,
  description: T.string,
  items: T.arrayOf(T.shape({
    id: T.string.isRequired
  })) // TODO : more precise type
}

export {
  PlayerStep
}
