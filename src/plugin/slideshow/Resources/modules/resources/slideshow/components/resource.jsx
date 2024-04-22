import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {Editor} from '#/plugin/slideshow/resources/slideshow/editor/components/editor'
import {Overview} from '#/plugin/slideshow/resources/slideshow/player/components/overview'
import {Player} from '#/plugin/slideshow/resources/slideshow/player/components/player'

const SlideshowResource = props =>
  <Resource
    {...omit(props, 'editable', 'showOverview')}
    styles={['claroline-distribution-plugin-slideshow-slideshow-resource']}
    overview={Overview}
    actions={[
      /*{
        name: 'play',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: `${props.path}/play`
      }*/
    ]}
    redirect={[
      {from: '/', exact: true, to: '/play', disabled: props.showOverview}
    ]}
    pages={[
      {
        path: '/play/:id?',
        render: (routeProps) => {
          const SlideshowPlayer = (
            <Player
              activeSlide={routeProps.match.params.id}
            />
          )

          return SlideshowPlayer
        }
      }, {
        path: '/edit',
        component: Editor,
        disabled: !props.editable
      }
    ]}
  />

SlideshowResource.propTypes = {
  path: T.string.isRequired,
  showOverview: T.bool.isRequired,
  editable: T.bool.isRequired
}

export {
  SlideshowResource
}
