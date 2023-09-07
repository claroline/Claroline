import React, { useState } from 'react'
import { useHistory, useRouteMatch } from 'react-router-dom'
import { PropTypes as T } from 'prop-types'

import { trans } from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import { ContentHtml } from '#/main/app/content/components/html'
import { ContentPlaceholder } from '#/main/app/content/components/placeholder'

import { FlashcardDeck } from '#/plugin/flashcard/resources/flashcard/prop-types'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

const Player = ({ deck, updateUserProgression, draw }) => {
  const history = useHistory()
  const match = useRouteMatch()
  const [isFlipped, setIsFlipped] = useState(false)
  const [currentCardIndex, setCurrentCardIndex] = useState(0)
  const currentCard = deck.cards[currentCardIndex]

  const goToNextCard = () => {
    const maxCards = draw > 0 ? Math.min(draw, deck.cards.length) : deck.cards.length
    const isLastCard = currentCardIndex + 1 === maxCards
    if (isLastCard && deck.end.display) {
      history.push(`${match.path}/end`)
    } else {
      const nextIndex = isLastCard ? 0 : currentCardIndex + 1
      setCurrentCardIndex(nextIndex)
    }
  }

  const handleAnswer = (isSuccessful) => {
    updateUserProgression(currentCard.id, isSuccessful).then(() => {
      setIsFlipped(false)
      setTimeout(goToNextCard, 100)
    })
  }

  const renderCardContent = (contentKey) => (
    <>
      {currentCard.question && <p className="card-element-question">{currentCard.question}</p>}
      <ContentHtml className="card-element-content">
        {currentCard[contentKey]}
      </ContentHtml>
    </>
  )

  if (!deck.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }

  return (
    <section>
      <ProgressBar
        className="progress-minimal"
        value={(currentCardIndex+1) / deck.cards.length * 100}
        size="xs"
        type="learning"
      />
      <div className="card-player">
        <div className="card-deck">
          <div className={`card-element card-element-0 ${isFlipped ? 'card-element-flip' : ''}`}>
            <div className="card-element-visible">
              {renderCardContent('visibleContent')}
            </div>
            <div className="card-element-hidden">
              {renderCardContent('hiddenContent')}
            </div>
          </div>
          { deck.cards.length > 1 && (!draw || currentCardIndex < draw - 1) && <div className="card-element card-element-1"></div> }
          { deck.cards.length > 2 && (!draw || currentCardIndex < draw - 2) && <div className="card-element card-element-2"></div> }
        </div>
      </div>

      <div className="card-buttons mt-5">
        { !isFlipped && <Button
          label={trans('show_answer', {}, 'flashcard')}
          type={CALLBACK_BUTTON}
          className="btn btn-info"
          callback={() => setIsFlipped(!isFlipped)}
        />
        }

        { isFlipped && <CallbackButton
          className="btn btn-success"
          callback={() => handleAnswer(true)}
        >
          {trans('right_answer', {}, 'flashcard')}
        </CallbackButton>
        }

        { isFlipped && <CallbackButton
          className="btn btn-danger"
          label={trans('wrong_answer', {}, 'flashcard')}
          callback={() => handleAnswer(false)}
        >
          {trans('wrong_answer', {}, 'flashcard')}
        </CallbackButton>
        }
      </div>
    </section>

  )
}

Player.propTypes = {
  deck: T.shape(
    FlashcardDeck.propTypes
  ).isRequired,
  draw: T.number,
  updateUserProgression: T.func.isRequired
}

export {
  Player
}
