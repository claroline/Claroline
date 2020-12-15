import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentHtml} from '#/main/app/content/components/html'

import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const PlayerStep = props => {
  const numbering = getNumbering(props.numbering, props.index)

  return (
    <div className="current-step">
      {props.showTitle &&
        <ContentTitle
          level={3}
          displayLevel={2}
          numbering={numbering}
          title={props.title ? props.title : trans('step', {number: props.index + 1}, 'quiz')}
        />
      }

      {props.description &&
        <ContentHtml className="step-description">{props.description}</ContentHtml>
      }

      {props.items.map(item =>
        <div key={item.id} className="item-player">{item.title}</div>
      )}
    </div>
  )
}

PlayerStep.propTypes = {
  numbering: T.string.isRequired,
  showTitle: T.bool,

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
