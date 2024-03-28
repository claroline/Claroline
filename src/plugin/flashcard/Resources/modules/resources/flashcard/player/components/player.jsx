import React, { useState, useCallback } from 'react'
import { useHistory, useRouteMatch } from 'react-router-dom'
import { PropTypes as T } from 'prop-types'

import {Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import { trans } from '#/main/app/intl/translation'
import {ProgressBar} from '#/main/app/content/components/progress-bar'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Player = props => {
  const history = useHistory()
  const match = useRouteMatch()
  const [isFlipped, setIsFlipped] = useState(false)
  const [isAnswering, setIsAnswering] = useState(false)
  const currentCardIndex = props.attempt.data.cardsAnsweredIds.length
  const maxCards = props.attempt.data.cardsSessionIds.length + props.attempt.data.cardsAnsweredIds.length
  const currentCardId = props.attempt.data.cardsSessionIds[0]
  const currentCardProgression = props.attempt.data.cards.find(card => card.id === currentCardId)
  const currentCard = currentCardProgression ? currentCardProgression.flashcard : null

  const goToNextCard = useCallback(() => {
    const isLastCard = props.attempt.data.cardsSessionIds.length <= 1

    if (isLastCard) {
      if (props.flashcardDeck.end.display) {
        history.push(`${match.path}/end`)
      } else {
        history.push(props.overview ? `${match.path}/overview` : props.path)
      }
    }
  }, [props.attempt.data.cardsSessionIds, maxCards, props.flashcardDeck.end.display, match.path, history])

  const handleAnswer = useCallback(
    (isSuccessful) => {
      if( currentCard !== null ) {
        setIsAnswering(true)
        props.updateProgression(currentCard.id, isSuccessful).then(() => {
          setIsAnswering(false)
          setIsFlipped(false)
          goToNextCard()
        })
      } else {
        setIsAnswering(false)
        setIsFlipped(false)
        goToNextCard()
      }
    },
    [props.updateProgression, setIsFlipped, goToNextCard, setIsAnswering]
  )

  if (!maxCards) {
    return <ContentPlaceholder size="lg" title={trans('no_card', {}, 'flashcard')} />
  }

  return (
    <>
      {props.flashcardDeck.showProgression &&
        <ProgressBar
          className="progress-minimal"
          value={(currentCardIndex+1) / maxCards * 100}
          size="xs"
          type="learning"
        />
      }

      <div className="flashcard-player content-sm mt-5">
        {props.flashcardDeck.showProgression &&
          <div className="flashcard-counter mb-1">
            <div>
              Session {`${props.attempt.data.session}`}
            </div>
            <div>
              {`${currentCardIndex + 1} / ${maxCards}`}
            </div>
          </div>
        }
        <div className="flashcard-deck">
          <Card
            card={currentCard}
            flipped={isFlipped}
            mode="play"
          />

          { maxCards > 1 && currentCardIndex < maxCards - 1 &&
            <div className="flashcard flashcard-1">
              <div className="flashcard-card" />
            </div>
          }
          { maxCards > 2 && currentCardIndex < maxCards - 2 &&
            <div className="flashcard flashcard-2">
              <div className="flashcard-card" />
            </div>
          }
        </div>

        <Toolbar
          className="flashcard-buttons d-flex gap-1 mt-5 mb-3"
          buttonName="btn"
          size="lg"
          actions={[
            {
              name: 'flip',
              type: CALLBACK_BUTTON,
              className: 'btn-primary w-100',
              label: trans('flip_card', {}, 'flashcard'),
              callback: () => setIsFlipped(!isFlipped),
              displayed: !isFlipped
            }, {
              name: 'answer-correct',
              type: CALLBACK_BUTTON,
              className: 'btn-success w-50',
              label: (props.flashcardDeck.customButtons && props.flashcardDeck.rightButtonLabel) || trans('right_answer', {}, 'flashcard'),
              callback: () => handleAnswer(true),
              displayed: isFlipped,
              disabled: isAnswering
            }, {
              name: 'answer-incorrect',
              type: CALLBACK_BUTTON,
              className: 'btn-danger w-50',
              label: (props.flashcardDeck.customButtons && props.flashcardDeck.wrongButtonLabel) || trans('wrong_answer', {}, 'flashcard'),
              callback: () => handleAnswer(false),
              displayed: isFlipped,
              disabled: isAnswering
            }
          ]}
        />
      </div>
    </>
  )
}

Player.propTypes = {
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  draw: T.number,
  path: T.string,
  updateProgression: T.func.isRequired,
  overview: T.bool,
  attempt: T.shape({
    data: T.shape({
      cardsAnsweredIds: T.arrayOf(T.number).isRequired,
      cards: T.arrayOf(
        T.shape({
          id: T.number.isRequired,
          flashcard: T.shape({
            id: T.string
          }).isRequired
        })
      ).isRequired,
      cardsSessionIds: T.arrayOf(T.number).isRequired,
      session: T.number.isRequired
    }).isRequired
  }).isRequired
}

export {
  Player
}
