import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const FlashcardDeckPlayer = props => {
  if (0 === props.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }

  return (
    <Fragment>
      <h2 className="sr-only">{trans('play')}</h2>
      <Routes
        routes={[
          {
            path: '/play/'
          }
        ]}
      />
    </Fragment>
  )
}

FlashcardDeckPlayer.propTypes = {
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  ))
}

export {
  FlashcardDeckPlayer
}
