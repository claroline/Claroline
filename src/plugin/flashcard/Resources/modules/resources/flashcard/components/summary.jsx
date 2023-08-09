import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentSummary} from '#/main/app/content/components/summary'
import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {FlashcardDeck as FlashcardDeckTypes} from "#/plugin/flashcard/resources/flashcard/prop-types";

const FlashcardDeckSummary = (props) => {
  if (0 === props.cards.length) {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_card', {}, 'flashcard')}
      />
    )
  }

  let baseLinks = []
  if (props.overview) {
    baseLinks = [{
      id: 'home',
      type: LINK_BUTTON,
      label: (
        <Fragment>
          {trans('home')}
          <span
            className="card-status">
            <span
              className={classes('fa fa-fw fa-home')}/>
          </span>
        </Fragment>
      ),
      target: props.basePath,
      exact: true,
      onClick: props.onNavigate
    }]
  }

  let endLink = []
  if (props.showEndPage) {
    endLink = [{
      id: 'end',
      type: LINK_BUTTON,
      label:(
        <Fragment>
          {trans('end')}
          <span className="card-status">
            <span className={classes('fa fa-fw fa-flag-checkered')} />
          </span>
        </Fragment>
      ),
      target: props.basePath + '/play/end',
      exact: true,
      onClick: props.onNavigate
    }]
  }

  return (
    <div className="flashcard-player row justify-content-start">
      <ul>
        {props.cards.map((card, index) => {
          return (
            <li className="card-preview" key={card.id}>
              <div className="card-header">
                {card.question && card.question.length > 0 &&
                  <strong>{card.question}</strong>
                }

                {(!card.question || card.question.length <= 0 ) &&
                  <strong>{trans('card_number',{},'flashcard') + ' ' + (index +1)}</strong>
                }
              </div>
            </li>
          )
        })}
      </ul>

      <ContentSummary
        className={props.className}
        links={baseLinks.concat(
        ).concat(endLink)}
        noCollapse={true}
      />
    </div>
  )
}

FlashcardDeckSummary.propTypes = {
  className: T.string,
  basePath: T.string.isRequired,
  cards: T.arrayOf(T.shape({
    id: T.string.isRequired,
    question: T.string
  })).isRequired,
  onNavigate: T.func,
  overview: T.bool,
  showEndPage: T.bool
}

export {
  FlashcardDeckSummary
}
