import React, { useState } from 'react'
import { useHistory, useRouteMatch } from 'react-router-dom'
import { PropTypes as T } from 'prop-types'

import { trans } from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import { ContentHtml } from '#/main/app/content/components/html'
import { ContentPlaceholder } from '#/main/app/content/components/placeholder'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

import { FlashcardDeck } from '#/plugin/flashcard/resources/flashcard/prop-types'

const Player = props => {
  const history = useHistory()
  const match = useRouteMatch()
  const [isFlipped, setIsFlipped] = useState(false)
  const [currentCardIndex, setCurrentCardIndex] = useState(0)
  const currentCard = props.flashcardDeck.cards[currentCardIndex]
  const maxCards = props.draw > 0 ? Math.min(props.draw, props.flashcardDeck.cards.length) : props.flashcardDeck.cards.length

  const goToNextCard = () => {
    const isLastCard = currentCardIndex + 1 === maxCards

    if (isLastCard && props.flashcardDeck.end.display) {
      history.push(`${match.path}/end`)
    } else {
      const nextIndex = isLastCard ? 0 : currentCardIndex + 1
      setCurrentCardIndex(nextIndex)
    }
  }

  const handleAnswer = (isSuccessful) => {
    props.updateProgression(currentCard.id, isSuccessful).then(() => {
      setIsFlipped(false)
      setTimeout(goToNextCard, 100)
    })
  }

  const renderCardContent = (contentKey) => (
    <>
      {currentCard.question && <p className="flashcard-element-question">{currentCard.question}</p>}
      <ContentHtml className="flashcard-element-content">
        {currentCard[contentKey]}
      </ContentHtml>
    </>
  )

  if (!maxCards) {
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
        value={(currentCardIndex+1) / maxCards * 100}
        size="xs"
        type="learning"
      />
      <div className="flashcard-player">
        <div className="flashcard-deck">
          <div className={`flashcard-element flashcard-element-0 ${isFlipped ? 'flashcard-element-flip' : ''}`}>
            <div className="flashcard-element-visible">
              {renderCardContent('visibleContent')}
            </div>
            <div className="flashcard-element-hidden">
              {renderCardContent('hiddenContent')}
            </div>
          </div>
          { maxCards > 1 && (!props.draw || currentCardIndex < props.draw - 1) && <div className="flashcard-element flashcard-element-1"></div> }
          { maxCards > 2 && (!props.draw || currentCardIndex < props.draw - 2) && <div className="flashcard-element flashcard-element-2"></div> }
        </div>
      </div>

      <div className="flashcard-buttons mt-5">
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
  flashcardDeck: T.shape(
    FlashcardDeck.propTypes
  ).isRequired,
  draw: T.number,
  updateProgression: T.func.isRequired
}

export {
  Player
}
