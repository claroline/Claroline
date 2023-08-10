import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ResourceEnd} from '#/main/core/resource/components/end'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store'

import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/store'
import {FlashcardDeckSummary} from '#/plugin/flashcard/resources/flashcard/components/summary'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {ContentHtml} from "#/main/app/content/components/html";

class PlayerEndComponent extends Component {
  render() {
    return (
      <div>
        <section className="resource-end my-3">
          {get(this.props.flashcardDeck, 'end.message') &&
            <section className="resource-info mb-3">
              <div className="card">
                {typeof get(this.props.flashcardDeck, 'end.message') === 'string' ?
                  <ContentHtml className="card-body">{get(this.props.flashcardDeck, 'end.message')}</ContentHtml>
                  :
                  <div className="card-body">{get(this.props.flashcardDeck, 'end.message')}</div>
                }
              </div>
            </section>
          }
        </section>

        <section className="resource-parameters mb-3">
          <h3 className="h2">{trans('summary')}</h3>
          <FlashcardDeckSummary
            className="component-container"
            basePath={this.props.path}
            cards={this.props.cards}
          />
        </section>
      </div>
    )
  }
}

PlayerEndComponent.propTypes = {
  path: T.string.isRequired,
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  cards: T.arrayOf(T.shape(
    FlashcardDeckTypes.propTypes
  ))
}

const PlayerEnd = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    flashcardDeck: baseSelectors.flashcardDeck(state),
    cards: selectors.cards(state)
  })
)(PlayerEndComponent)

export {
  PlayerEnd
}
