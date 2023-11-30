import React from 'react'
import { trans } from '#/main/app/intl'

export const getIcon = (index) => {
  if (index > 1 && index < 7) {
    return <span className={'fa fa-fw fa-circle-check'}/>
  } else if (index === 7) {
    return <span className={'fa fa-fw fa-flag-checkered'}/>
  }
  return <span className={'fa fa-fw fa-map-marker'}/>
}

export const getRule = (index) => {
  switch (index) {
    case 1: return trans('session_cards_all', {}, 'flashcard')
    case 2:
    case 4: return trans('session_cards_1x', {}, 'flashcard') + ' + ' + trans('session_cards_failed', {}, 'flashcard')
    case 3: return trans('session_cards_2x', {}, 'flashcard') + ' + ' + trans('session_cards_failed', {}, 'flashcard')
    case 5: return trans('session_cards_failed', {}, 'flashcard')
    case 6: return trans('session_cards_1x', {}, 'flashcard') + ' + ' + trans('session_cards_2x', {}, 'flashcard') + ' + ' + trans('session_cards_failed', {}, 'flashcard')
    case 7: return trans('session_cards_3x', {}, 'flashcard') + ' + ' + trans('session_cards_failed', {}, 'flashcard')
    default: return index
  }
}

export const getClassList = (index, session, started, completed) => {
  return [
    'flashcard-timeline-step',
    index < session ? 'flashcard-timeline-step-done' : '',
    session === index && completed ? 'flashcard-timeline-step-done' : '',
    session === index && started && !completed ? 'flashcard-timeline-step-current' : ''
  ]
}

export const getLabel = (index, session, started, completed) => {
  if (index < session || (session === index && completed)) {
    return <span className={'fa fa-fw fa-check'}></span>
  } else if (session === index && started) {
    return <span className={'fa fa-fw fa-hourglass-start'}></span>
  }
  return index
}

export const getProgression = (session, started, completed, end) => {
  if (end) {
    return (session - 2) * (100 / 6)
  } else if (started || completed) {
    return (session - 1) * (100 / 6)
  }
  return (session - 1.5) * (100 / 6)
}
