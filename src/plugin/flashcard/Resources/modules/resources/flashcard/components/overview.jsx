import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'

import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {FlashcardDeckSummary} from '#/plugin/flashcard/resources/flashcard/components/summary'

const FlashcardDeckOverview = (props) =>
  <ResourceOverview
    contentText={get(props.flashcardDeck, 'overview.message')}
    actions={[
      {
        type: LINK_BUTTON,
        label: trans('start', {}, 'actions'),
        target: `${props.basePath}/play`,
        primary: true,
        disabled: props.empty,
        disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')]:[]
      }
    ]}
  >
    <section className="resource-parameters mb-3">
      {!isEmpty(get(props.flashcardDeck, 'overview.resource')) &&
        <ResourceEmbedded
          className="step-primary-resource"
          resourceNode={get(props.flashcardDeck, 'overview.resource')}
          showHeader={false}
        />
      }

      {!isEmpty(props.flashcardDeck.cards) &&
        <>
          <h3 className="h2">{trans('cards_list',{},'flashcard')}</h3>
          <FlashcardDeckSummary
            className="component-container"
            basePath={props.basePath}
            cards={props.cards}
            overview={props.overview}
            showEndPage={props.showEndPage}
          />
        </>
      }

      {isEmpty(props.flashcardDeck.cards) &&
        <ContentPlaceholder
          size="lg"
          icon="fa fa-image"
          title={trans('no_card', {}, 'flashcard')}
        />
      }
    </section>
  </ResourceOverview>

FlashcardDeckOverview.propTypes = {
  basePath: T.string.isRequired,
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  cards: T.arrayOf(T.shape(
    FlashcardDeckTypes.propTypes,
  )),
  overview: T.bool.isRequired,
  showEndPage: T.bool.isRequired,
  empty: T.bool.isRequired
}

FlashcardDeckOverview.defaultProps = {
  empty: true
}

export {
  FlashcardDeckOverview
}
