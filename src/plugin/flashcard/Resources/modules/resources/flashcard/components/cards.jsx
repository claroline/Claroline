import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {trans} from '#/main/app/intl'

const Cards = props =>
  <ul className="cards">
    {props.cards.map((card, index) =>
      <li key={card.id} className="card-preview">
        {props.actions && (
          <div className="card-actions">
            <Toolbar
              id={`${card.id}-btn`}
              className="slide-actions"
              buttonName="btn"
              tooltip="bottom"
              size="sm"
              toolbar="more"
              actions={props.actions(card)}
            />
          </div>
        )}
        <div className="card-header">
          {card.question && card.question.length > 0 &&
            <strong>{card.question}</strong>
          }

          {(!card.question || card.question.length <= 0 ) &&
            <strong>{trans('card_number',{},'flashcard') + ' ' + (index +1)}</strong>
          }
        </div>
      </li>
    )}
  </ul>

Cards.propTypes = {
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  )),
  actions: T.func
}

export {
  Cards
}
