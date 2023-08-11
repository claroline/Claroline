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

const FlashcardDeckPlayer = ({ deck, updateUserProgression }) => {
  const [currentCardIndex, setCurrentCardIndex] = useState(0)
  const history = useHistory()
  const match = useRouteMatch()
  const [isFlipped, setIsFlipped] = useState(false)
  const currentCard = deck.cards[currentCardIndex]

  const goToNextCard = () => {
    const isLastCard = currentCardIndex + 1 === deck.cards.length
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
      <div className={'card-element-counter'}>
        {`${currentCardIndex + 1} / ${deck.cards.length}`}
      </div>
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
    <section className="card-player">
      <div className="card-deck">
        <div className={`card-element card-element-0 ${isFlipped ? 'card-element-flip' : ''}`}>
          <div className="card-element-visible">
            {renderCardContent('visibleContent')}
            <div className="card-element-buttons">
              <Button
                label={trans('show_answer', {}, 'flashcard')}
                type={CALLBACK_BUTTON}
                className="btn btn-info"
                callback={() => setIsFlipped(!isFlipped)}
              />
            </div>
          </div>
          <div className="card-element-hidden">
            {renderCardContent('hiddenContent')}
            <div className="card-element-buttons mt-3">
              <CallbackButton
                className="btn btn-success"
                callback={() => handleAnswer(true)}
              >
                {trans('right_answer', {}, 'flashcard')}
              </CallbackButton>
              <CallbackButton
                className="btn btn-danger"
                label={trans('wrong_answer', {}, 'flashcard')}
                callback={() => handleAnswer(false)}
              >

                {trans('wrong_answer', {}, 'flashcard')}
              </CallbackButton>
            </div>
          </div>
        </div>
        {deck.cards.length > 1 && <div className="card-element card-element-1"></div>}
        {deck.cards.length > 2 && <div className="card-element card-element-2"></div>}
      </div>
    </section>
  )
}

FlashcardDeckPlayer.propTypes = {
  deck: T.shape(
    FlashcardDeck.propTypes
  ).isRequired,
  updateUserProgression: T.func.isRequired
}

export {
  FlashcardDeckPlayer
}
