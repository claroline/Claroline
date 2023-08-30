import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Cards = props =>
  <ul className="cards">
    {props.cards.map((card) =>
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
          <strong>{card.question}</strong>
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
