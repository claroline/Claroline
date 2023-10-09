import React, { useState, useCallback } from 'react'
import { useHistory, useRouteMatch } from 'react-router-dom'
import { PropTypes as T } from 'prop-types'

import { trans } from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import { ContentPlaceholder } from '#/main/app/content/components/placeholder'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import { FlashcardDeck } from '#/plugin/flashcard/resources/flashcard/prop-types'

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
        className="mb-3"
        value={(currentCardIndex+1) / maxCards * 100}
        size="xs"
        type="learning"
      />
      <div className="flashcard-player">
        <div className="flashcard-deck">
          <div className={`flashcard flashcard-0 ${isFlipped ? 'flashcard-flip' : ''}`}>
            <div className="flashcard-visible">
              <Card
                card={currentCard}
                contentKey="visibleContent"
              />
            </div>
            <div className="flashcard-hidden">
              <Card
                card={currentCard}
                contentKey="hiddenContent"
              />
            </div>
          </div>
          { maxCards > 1 && (!props.draw || currentCardIndex < props.draw - 1) &&
            <div className="flashcard flashcard-1"></div>
          }
          { maxCards > 2 && (!props.draw || currentCardIndex < props.draw - 2) &&
            <div className="flashcard flashcard-2"></div>
          }
        </div>
      </div>

      <div className="flashcard-buttons mt-5">
        { !isFlipped && (
          <Button
            className="btn btn-primary"
            label={trans('show_answer', {}, 'flashcard')}
            type={CALLBACK_BUTTON}
            size="lg"
            callback={() => setIsFlipped(!isFlipped)}
          />
        )}

        { isFlipped && (
          <Button
            className="btn btn-success"
            label={trans('right_answer', {}, 'flashcard')}
            type={CALLBACK_BUTTON}
            size="lg"
            callback={() => handleAnswer(true)}
          />
        )}

        { isFlipped && (
          <Button
            className="btn btn-danger"
            label={trans('wrong_answer', {}, 'flashcard')}
            type={CALLBACK_BUTTON}
            size="lg"
            callback={() => handleAnswer(false)}
          />
        )}
      </div>
    </>

  )
}

Player.propTypes = {
  flashcardDeck: T.shape(
    FlashcardDeck.propTypes
  ).isRequired,
  draw: T.number,
  updateProgression: T.func.isRequired
}

export {
  Player
}
