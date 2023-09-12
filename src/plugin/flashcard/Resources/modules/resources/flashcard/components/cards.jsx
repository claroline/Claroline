import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Cards = props =>
  <ul className="flashcards">
    {props.cards.map((card) =>
      <li key={card.id} className="flashcard-preview card">
        <div className="flashcard-content">
          {props.actions && (
            <div className="flashcard-actions">
              <Toolbar
                id={`${card.id}-btn`}
                className="flashcard-actions"
                buttonName="btn"
                tooltip="bottom"
                size="sm"
                toolbar="more"
                actions={props.actions(card)}
              />
            </div>
          )}
          <div className="card-body">
            <ContentHtml>{card.visibleContent}</ContentHtml>
          </div>
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
