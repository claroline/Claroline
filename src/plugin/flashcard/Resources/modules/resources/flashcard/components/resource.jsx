import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Editor} from '#/plugin/flashcard/resources/flashcard/editor/components/editor'
import {Overview} from '#/plugin/flashcard/resources/flashcard/player/components/overview'
import {Player} from '#/plugin/flashcard/resources/flashcard/player/components/player'

const FlashcardDeckResource = props =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        displayed: props.showOverview,
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
        path: '/',
        exact: true,
        disabled: !props.showOverview,
        component: Overview
      }, {
        path: '/play/:id?',
        render: (routeProps) => {
          const FlashcardPlayer = (
            <Player
              activeCard={routeProps.match.params.id}
            />
          )

          return FlashcardPlayer
        }
      }, {
        path: '/edit',
        component: Editor,
        disabled: !props.editable
      }
    ]}
    redirect={[
      {from: '/', exact: true, to: '/play', disabled: props.showOverview}
    ]}
  />

FlashcardDeckResource.propTypes = {
  path: T.string,
  showOverview: T.bool,
  editable: T.bool
}

export {
  FlashcardDeckResource
}
