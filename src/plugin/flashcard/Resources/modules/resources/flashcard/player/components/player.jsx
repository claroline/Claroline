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
  const [currentCardIndex, setCurrentCardIndex] = useState(0)
  const currentCard = props.flashcardDeck.cards[currentCardIndex]
  const maxCards = props.draw > 0 ? Math.min(props.draw, props.flashcardDeck.cards.length) : props.flashcardDeck.cards.length

  const goToNextCard = useCallback(() => {
    const isLastCard = currentCardIndex + 1 === maxCards

    if (isLastCard && props.flashcardDeck.end.display) {
      history.push(`${match.path}/end`)
    } else {
      const nextIndex = isLastCard ? 0 : currentCardIndex + 1
      setCurrentCardIndex(nextIndex)
    }
  }, [currentCardIndex, maxCards, props.flashcardDeck.end.display, match.path, history])

  const handleAnswer = useCallback(
    (isSuccessful) => {
      props.updateProgression(currentCard.id, isSuccessful).then(() => {
        setIsFlipped(false)
        setTimeout(goToNextCard, 100)
      })
    },
    [props.updateProgression, currentCard.id, setIsFlipped, goToNextCard]
  )

  if (!maxCards) {
    return <ContentPlaceholder size="lg" title={trans('no_card', {}, 'flashcard')} />
  }

  return (
    <>
      <ProgressBar
        className="mb-3 progress-minimal"
        value={(currentCardIndex+1) / maxCards * 100}
        size="xs"
        type="learning"
      />

      <div className="flashcard-player content-sm">
        <div className="flashcard-deck">
          <div className={`flashcard flashcard-0 ${isFlipped ? 'flashcard-flip' : ''}`}>
            <Card
              className="flashcard-visible"
              card={currentCard}
              contentKey="visibleContent"
            />
            <Card
              className="flashcard-hidden"
              card={currentCard}
              contentKey="hiddenContent"
            />
          </div>

          { maxCards > 1 && (!props.draw || currentCardIndex < props.draw - 1) &&
            <div className="flashcard flashcard-1">
              <div className="flashcard-card" />
            </div>
          }
          { maxCards > 2 && (!props.draw || currentCardIndex < props.draw - 2) &&
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
              label: trans('show_answer', {}, 'flashcard'),
              callback: () => setIsFlipped(!isFlipped),
              displayed: !isFlipped
            }, {
              name: 'answer-correct',
              type: CALLBACK_BUTTON,
              className: 'btn-success w-50',
              label: trans('right_answer', {}, 'flashcard'),
              callback: () => handleAnswer(true),
              displayed: isFlipped
            }, {
              name: 'answer-incorrect',
              type: CALLBACK_BUTTON,
              className: 'btn-danger w-50',
              label: trans('wrong_answer', {}, 'flashcard'),
              callback: () => handleAnswer(false),
              displayed: isFlipped
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
  updateProgression: T.func.isRequired
}

export {
  Player
}
