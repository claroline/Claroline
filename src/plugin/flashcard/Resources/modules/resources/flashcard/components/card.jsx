import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Card = (props) => {

  const renderCardContent = (card, contentKey) => (
    <>
      {props.actions &&
        <Toolbar
          id={`${card.id}-btn`}
          buttonName="action-button"
          tooltip="right"
          size="sm"
          toolbar="more"
          className="flashcard-actions"
          actions={props.actions(card)}
        />
      }
      {card.question && (
        <div className="flashcard-question">
          {card.question}
        </div>
      )}
      <div className="flashcard-content">
        { card[contentKey+'Type'] === 'text' &&
          <ContentHtml>{card[contentKey]}</ContentHtml>
        }

        { card[contentKey+'Type'] === 'image' &&
          <img src={asset(card[contentKey].url)} alt={card.question} className="flashcard-media" />
        }

        { card[contentKey+'Type'] === 'video' && (
          <video controls={true} className="flashcard-media not-video-js vjs-default-skin vjs-16-9">
            <source src={asset(card[contentKey].url)} type={card.type}/>
          </video>
        )}

        { card[contentKey+'Type'] === 'audio' && (
          <audio controls={true}>
            <source src={asset(card[contentKey].url)} type={card.type}/>
          </audio>
        )}
      </div>

    </>
  )

  return renderCardContent(props.card, (props.contentKey !== null && props.contentKey !== undefined) ? props.contentKey : 'visibleContent')
}

Card.propTypes = {
  card: T.shape(
    CardTypes.propTypes
  ),
  actions: T.func
}

export {
  Card
}
