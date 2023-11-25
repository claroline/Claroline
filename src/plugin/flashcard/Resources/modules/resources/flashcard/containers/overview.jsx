import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store'
import {Overview as OverviewComponent} from '#/plugin/flashcard/resources/flashcard/components/overview'

const Overview = connect(
  (state) => ({
    basePath: resourceSelectors.path(state),
    flashcardDeck: selectors.flashcardDeck(state),
    evaluation: resourceSelectors.resourceEvaluation(state),
    attempt: selectors.attempt(state),
    cards: selectors.cards(state),
    empty: selectors.empty(state),
    overview: selectors.showOverview(state),
    resourceNode: resourceSelectors.resourceNode(state),
    showEndPage: selectors.showEndPage(state)
  })
)(OverviewComponent)

export {
  Overview
}
