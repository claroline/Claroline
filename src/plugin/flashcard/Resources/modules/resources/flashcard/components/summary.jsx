import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const FlashcardDeckSummary = (props) => {
  if (0 === props.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }
  return (
    <div className="flashcard-player row justify-content-start">
      <ul>
        {props.cards.map((card, index) => {
          return (
            <li className="card-preview" key={card.id}>
              <div className="card-header">
                {card.question && card.question.length > 0 &&
                  <strong>{card.question}</strong>
                }

                {(!card.question || card.question.length <= 0 ) &&
                  <strong>{trans('card_number',{},'flashcard') + ' ' + (index +1)}</strong>
                }
              </div>
            </li>
          )
        })}
      </ul>
    </div>
  )
}

FlashcardDeckSummary.propTypes = {
  cards: T.arrayOf(T.shape({
    id: T.string.isRequired,
    question: T.string
  })).isRequired
}

export {
  FlashcardDeckSummary
}
