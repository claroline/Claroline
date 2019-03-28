import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'

import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const PlayerStep = props => {
  const numbering = getNumbering(props.numbering, props.index)

  return (
    <div className="current-step">
      <h3 className="h2 step-title">
        {numbering &&
          <span className="h-numbering">{numbering}</span>
        }

        {props.title ? props.title : trans('step', {number: props.index + 1}, 'quiz')}
      </h3>

      {props.description &&
        <HtmlText className="step-description">{props.description}</HtmlText>
      }

      {props.items.map(item =>
        <div key={item.id} className="item-player">{item.title}</div>
      )}
    </div>
  )
}

PlayerStep.propTypes = {
  numbering: T.string.isRequired,

  index: T.number.isRequired,
  title: T.string,
  description: T.string,
  items: T.arrayOf(T.shape({
    id: T.string.isRequired
  })) // TODO : more precise type
}

export {
  PlayerStep
}
