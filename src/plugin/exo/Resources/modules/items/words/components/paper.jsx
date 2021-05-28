import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'

import {WordsAnswer} from '#/plugin/exo/items/words/components/answer'
import {WordsSolutions} from '#/plugin/exo/items/words/components/solutions'
import {WordsStats} from '#/plugin/exo/items/words/components/stats'

const WordsPaper = (props) =>
  <PaperTabs
    showExpected={props.showExpected}
    showStats={props.showStats}
    showYours={props.showYours}
    id={props.item.id}
    yours={
      <WordsAnswer
        text={props.answer}
        contentType={props.item.contentType}
        solutions={props.item.solutions}
        showScore={props.showScore}
        hasExpectedAnswers={props.item.hasExpectedAnswers}
      />
    }
    expected={
      <WordsSolutions
        className="words-paper"
        contentType={props.item.contentType}
        answers={props.item.solutions}
        showScore={props.showScore}
        hasExpectedAnswers={false}
      />
    }
    stats={
      <WordsStats
        contentType={props.item.contentType}
        solutions={props.item.solutions}
        hasExpectedAnswers={props.item.hasExpectedAnswers}
        stats={props.stats}
      />
    }
  />

WordsPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    solutions: T.arrayOf(T.object),
    contentType: T.string.isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.string.isRequired,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    words: T.object,
    unanswered: T.number,
    total: T.number
  })
}

WordsPaper.defaultProps = {
  answer: ''
}

export {
  WordsPaper
}
