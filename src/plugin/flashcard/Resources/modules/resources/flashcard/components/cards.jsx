import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
const Cards = props =>
  <ul className="slides">
    {props.cards.map(card =>
      <li key={card.id} className="slide-preview">
        <img src={asset(card.content.text)} alt={card.title} className="img-thumbnail" />

        {props.actions &&
          <Toolbar
            id={`${card.id}-btn`}
            className="slide-actions"
            buttonName="btn"
            tooltip="bottom"
            size="sm"
            toolbar="more"
            actions={props.actions(card)}
          />
        }
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
