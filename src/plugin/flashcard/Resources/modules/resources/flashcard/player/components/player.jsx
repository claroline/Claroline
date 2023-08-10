import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const FlashcardDeckPlayer = props => {
  if (0 === props.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }

  return (
    <section className="card-player" style={{ display: 'flex', justifyContent : 'center', alignItems: "center" }}>
      <div className="card-deck">
        <div className={'card-element card-element-0'}>

          <div className="card-element-visible">
            { props.cards[0].question.length > 0 &&
              <p className="card-element-question">
                {props.cards[0].question}
              </p>
            }
            <p className="card-element-content">
              {props.cards[0].visibleContent}
            </p>
            <div className="card-element-buttons">
              <Button
                type={CALLBACK_BUTTON}
                className="btn btn-info"
                callback={() => {
                  document.querySelector('.card-element-0').classList.toggle('card-element-flip')
                }}
              >
                {trans('show_answer', {}, 'flashcard')}
              </Button>
            </div>
          </div>

          <div className="card-element-hidden">
            { props.cards[0].question.length > 0 &&
              <p className="card-element-question">
                {props.cards[0].question}
              </p>
            }
            <p>
              {props.cards[0].hiddenContent}
            </p>
            <div className="card-element-buttons">
              <Button
                type={CALLBACK_BUTTON}
                className="btn btn-success"
                callback={() => {
                  // TODO : Next card + status change
                }}
              >
                {trans('right_answer', {}, 'flashcard')}
              </Button>
              <Button
                type={CALLBACK_BUTTON}
                className="btn btn-danger"
                callback={() => {
                  // TODO : Next card + status change
                }}
              >
                {trans('wrong_answer', {}, 'flashcard')}
              </Button>
            </div>
          </div>
        </div>

        { props.cards.length > 1 &&
          <div className={'card-element card-element-1'}></div>
        }

        { props.cards.length > 2 &&
          <div className={'card-element card-element-2'}></div>
        }
      </div>
    </section>
  )
}

FlashcardDeckPlayer.propTypes = {
  cards: T.arrayOf(T.shape(
    CardTypes.propTypes
  ))
}

export {
  FlashcardDeckPlayer
}
