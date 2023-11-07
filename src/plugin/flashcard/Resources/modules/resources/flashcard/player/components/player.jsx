import React, { useState, useCallback } from 'react'
import { useHistory, useRouteMatch } from 'react-router-dom'
import { PropTypes as T } from 'prop-types'

import { trans } from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Player = props => {
  const history = useHistory()
  const match = useRouteMatch()
  const [isFlipped, setIsFlipped] = useState(false)
  const [isAnswering, setIsAnswering] = useState(false)
  const [currentCardIndex, setCurrentCardIndex] = useState(props.attempt?.data?.nextCardIndex ?? 0)
  const maxCards = props.draw > 0 ? Math.min(props.draw, props.flashcardProgression.length) : props.flashcardProgression.length
  const currentCard = props.flashcardProgression[currentCardIndex] ? props.flashcardProgression[currentCardIndex].flashcard : props.flashcardProgression[0].flashcard

  const goToNextCard = useCallback(() => {
    const isLastCard = currentCardIndex + 1 === maxCards

    if (isLastCard) {
      if (props.flashcardDeck.end.display) {
        history.push(`${match.path}/end`)
      } else {
        history.push(props.overview ? `${match.path}/overview` : props.path)
      }
    } else {
      const nextIndex = isLastCard ? 0 : currentCardIndex + 1
      setCurrentCardIndex(nextIndex)
    }
  }, [currentCardIndex, maxCards, props.flashcardDeck.end.display, match.path, history])

  const handleAnswer = useCallback(
    (isSuccessful) => {
      setIsAnswering(true)
      props.updateProgression(currentCard.id, isSuccessful).then(() => {
        setIsAnswering(false)
        setIsFlipped(false)
        goToNextCard()
      })
    },
    [props.updateProgression, currentCard.id, setIsFlipped, goToNextCard, setIsAnswering]
  )

  if (!maxCards) {
    return <ContentPlaceholder size="lg" title={trans('no_card', {}, 'flashcard')} />
  }

  return (
    <>
      {props.flashcardDeck.showProgression &&
        <ProgressBar
          className="mb-3 progress-minimal"
          value={(currentCardIndex+1) / maxCards * 100}
          size="xs"
          type="learning"
        />
      }

      <div className="flashcard-player content-sm">
        {props.flashcardDeck.showProgression &&
          <div className="flashcard-counter mt-3">
            {/*Test affichage*/}
            Attempt #{`${props.attempt?.id}`} -
            Session {`${props.attempt?.data?.session}`} -
            {`${currentCardIndex + 1} / ${maxCards}`}
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
  overview: T.bool
}

export {
  Player
}
