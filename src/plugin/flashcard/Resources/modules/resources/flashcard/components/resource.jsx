import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {FlashcardDeckOverview} from '#/plugin/flashcard/resources/flashcard/containers/overview'
import {Editor} from '#/plugin/flashcard/resources/flashcard/editor/components/editor'
import {FlashcardDeckPlayer} from '#/plugin/flashcard/resources/flashcard/player/containers/player'
import {PlayerEnd} from '#/plugin/flashcard/resources/flashcard/player/components/end'

const FlashcardDeckResource = props =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: props.path,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: `${props.path}/play`
      }
    ]}
    routes={[
      {
        path: '/edit',
        component: Editor,
        disabled: !props.editable
      }, {
        path: '/play/end',
        exact: true,
        component: PlayerEnd
      }, {
        path: '/play',
        component: FlashcardDeckPlayer
      }, {
        path: '/',
        exact: true,
        component: FlashcardDeckOverview,
        disabled: !props.overview
      }
    ]}
    redirect={[
      {from: '/', exact: true, to: '/play', disabled: props.overview}
    ]}
  />

FlashcardDeckResource.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  overview: T.bool.isRequired
}

export {
  FlashcardDeckResource
}
