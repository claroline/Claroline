import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Video} from '#/main/app/components/video'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Card = (props) => {

  const renderCardContent = (card, contentKey) => (
    <div className={classes('flashcard-card', props.className)}>
      {card.question && (
        <h5 className="flashcard-question">
          {card.question}
        </h5>
      )}
      <div className="flashcard-content">
        { card[contentKey+'Type'] === 'text' &&
          <ContentHtml>{card[contentKey]}</ContentHtml>
        }

        { card[contentKey+'Type'] === 'image' &&
          <img src={asset(card[contentKey].url)} alt={card.question} className="flashcard-media" />
        }

        { card[contentKey+'Type'] === 'video' && (
          <Video
            className="flashcard-video"
            options={{
              controls: true,
              responsive: true,
              fluid: true
            }}
            sources={[{
              src: asset(card[contentKey].url)
            }]}
          />
        )}

        { card[contentKey+'Type'] === 'audio' && (
          <audio controls={true}>
            <source src={asset(card[contentKey].url)} type={card.type}/>
          </audio>
        )}
      </div>

      {props.actions &&
        <Toolbar
          id={`${card.id}-btn`}
          buttonName="btn btn-text-body action-button"
          tooltip="bottom"

          className="flashcard-actions"
          actions={props.actions(card)}
        />
      }
    </div>
  )

  return renderCardContent(props.card, (props.contentKey !== null && props.contentKey !== undefined) ? props.contentKey : 'visibleContent')
}

Card.propTypes = {
  className: T.string,
  card: T.shape(
    CardTypes.propTypes
  ),
  actions: T.func
}

export {
  Card
}
