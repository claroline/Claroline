import React from 'react'
import {PropTypes as T}  from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Video} from '#/main/app/components/video'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Card = props => {
  const contentKey = props.flipped ? 'hiddenContent' : 'visibleContent'
  let modeClasses = ''

  if( props.mode === 'preview' ) {
    modeClasses = `flashcard flashcard-preview ${props.flipped ? 'flashcard-flip' : ''}`
  } else if( props.mode === 'play' ) {
    modeClasses = `flashcard flashcard-0 ${props.flipped ? 'flashcard-flip' : ''}`
  }

  if(props.card === null) {
    return (
      <div></div>
    )
  }

  return (
    <div className={modeClasses}>
      <div className={classes('flashcard-card', props.className)}>
        {props.card.question && (
          <h5 className="flashcard-question">
            {props.card.question}
          </h5>
        )}
        <div className="flashcard-content">
          { props.card[contentKey+'Type'] === 'text' &&
            <ContentHtml>{props.card[contentKey]}</ContentHtml>
          }
          { props.card[contentKey] !== null && props.card[contentKey+'Type'] === 'image' &&
            <img src={asset(props.card[contentKey].url)} alt={props.card.question} className="flashcard-media" />
          }
          { props.card[contentKey] !== null && props.card[contentKey+'Type'] === 'video' && (
            <Video
              className="flashcard-video"
              options={{
                controls: true,
                responsive: true,
                fluid: true
              }}
              sources={[{
                src: asset(props.card[contentKey].url)
              }]}
            />
          )}
          { props.card[contentKey] !== null && props.card[contentKey+'Type'] === 'audio' && (
            <audio controls={true}>
              <source src={asset(props.card[contentKey].url)} type={props.card.type}/>
            </audio>
          )}
        </div>

        {props.actions &&
          <Toolbar
            id={`${props.card.id}-btn`}
            buttonName="btn btn-text-body action-button"
            tooltip="bottom"
            className="flashcard-actions"
            actions={props.actions(props.card)}
          />
        }
      </div>
    </div>
  )
}

Card.propTypes = {
  className: T.string,
  mode: T.string,
  flipped: T.bool,
  actions: T.func,
  card: T.shape(
    CardTypes.propTypes
  ).isRequired
}

export {
  Card
}
