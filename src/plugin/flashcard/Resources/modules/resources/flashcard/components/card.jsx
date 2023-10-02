import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Card = props =>
  <ul className="flashcards">
    {props.cards.map((card) =>
      <li key={card.id} className="flashcard-preview">
        {props.actions &&
          <div className="flashcard-actions">
            <Toolbar
              id={`${card.id}-btn`}
              buttonName="action-button"
              tooltip="right"
              size="sm"
              toolbar="more"
              actions={props.actions(card)}
            />
          </div>
        }
        { card.visibleContentType === 'image' &&
          <img src={asset(card.visibleContent.url)} alt={card.question} className="flashcard-thumbnail" />
        }
        { card.visibleContentType === 'video' &&
          <video controls={true}  className="flashcard-thumbnail">
            <source src={asset(card.visibleContent.url)} />
          </video>
        }
        { card.visibleContentType === 'audio' &&
          <audio controls controlsList="noremoteplayback nodownload noplaybackrate">
            <source src={asset(card.visibleContent.url)}/>
          </audio>
        }
        { card.visibleContentType === 'text' &&
          <div className="flashcard-content">
            <ContentHtml>{card.visibleContent}</ContentHtml>
          </div>
        }
      </li>
    )}
  </ul>

Card.propTypes = {
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  )),
  actions: T.func
}

export {
  Card
}
