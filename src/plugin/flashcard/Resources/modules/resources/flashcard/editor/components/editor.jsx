import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as formActions} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

const EditorComponent = props =>
  console.log('EditorComponent props', props) ||
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={(flashcardDeck) =>['apiv2_flashcard_deck_update', { id: props.flashcardDeck.id }]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-home',
        title: trans('overview'),
        fields: [
          {
            name: 'display.showOverview',
            type: 'boolean',
            label: trans('enable_overview'),
            linked: [
              {
                name: 'display.description',
                type: 'html',
                label: trans('overview_message'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'display.showOverview')
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.showControls',
            type: 'boolean',
            label: trans('show_controls', {}, 'flashcard')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-play',
        title: trans('playback'),
        fields: [
          {
            name: 'interval',
            type: 'number',
            label: trans('slide_duration', {}, 'flashcard'),
            options: {
              unit: 'ms'
            }
          }, {
            name: 'autoPlay',
            type: 'boolean',
            label: trans('auto_play', {}, 'flashcard')
          }
        ]
      }
    ]}
  />

EditorComponent.propTypes = {
  path: T.string.isRequired
}

EditorComponent.defaultProps = {
  cards: []
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    cards: selectors.cards(state)
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
    }
  })
)(EditorComponent)

export {
  Editor
}





