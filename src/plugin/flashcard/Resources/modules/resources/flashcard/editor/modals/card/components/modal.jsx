import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/store'

const CardModal = props => {

  let visibleContentTypes = []
  if (props.card && -1 !== ['audio'].indexOf(props.card.visibleContentType)) {
    visibleContentTypes = ['audio/*']
  } else if (props.card && -1 !== ['video'].indexOf(props.card.visibleContentType)) {
    visibleContentTypes = ['video/*']
  } else if (props.card && -1 !== ['image'].indexOf(props.card.visibleContentType)) {
    visibleContentTypes = ['image/*']
  } else {
    visibleContentTypes = ['image/*', 'video/*', 'audio/*']
  }

  let hiddenContentTypes = []
  if (props.card && -1 !== ['audio'].indexOf(props.card.hiddenContentType)) {
    hiddenContentTypes = ['audio/*']
  } else if (props.card && -1 !== ['video'].indexOf(props.card.hiddenContentType)) {
    hiddenContentTypes = ['video/*']
  } else if (props.card && -1 !== ['image'].indexOf(props.card.hiddenContentType)) {
    hiddenContentTypes = ['image/*']
  }

  return <Modal
    {...omit(props, 'card', 'formData', 'isNew', 'saveEnabled', 'reset', 'save')}
    icon={classes('fa fa-fw', {
      'fa-plus': props.isNew,
      'fa-cog': !props.isNew
    })}
    title={trans(props.isNew ? 'new_card' : 'card_parameters', {}, 'flashcard')}
    subtitle={get(props.card, 'meta.title')}
    onEntering={() => {
      if (props.card) {
        props.reset(props.card)
      } else {
        props.reset(Object.assign({}, CardTypes.defaultProps, {id: makeId()}), true)
      }
    }}
    onExiting={() => {
      props.reset(Object.assign({}, CardTypes.defaultProps, {id: makeId()}), true)
    }}
    size="lg"
    flush={true}
  >
    <FormData
      level={5}
      name={selectors.STORE_NAME}
      definition={[{
        icon: 'fa fa-fw fa-circle-info',
        title: trans('information'),
        fields: [{
          name: 'question',
          label: trans('question', {}, 'flashcard'),
          type: 'string'
        }, {
          name: 'visibleContentType',
          label: trans('visible_content_type', {}, 'flashcard'),
          type: 'choice',
          required: true,
          onChange: (newType) => {
            if (!props.card) return
            const newCard = JSON.parse(JSON.stringify(props.card))
            newCard.visibleContentType = newType
            newCard.visibleContent = newType === 'text' ? '' : null
            props.reset(newCard)
          },
          options: {
            condensed: true,
            choices: {
              text: trans('text'),
              image: trans('image'),
              video: trans('video'),
              audio: trans('audio')
            }
          },
          linked: [{
            name: 'visibleContent',
            type: 'file',
            label: trans('file'),
            hideLabel: true,
            displayed: (card) => -1 !== visibleContentTypes.indexOf(card.visibleContentType),
            options: {
              types: visibleContentTypes
            }
          }, {
            name: 'visibleContent',
            label: trans('visible_content', {}, 'flashcard'),
            type: 'html',
            displayed: (card) => -1 !== ['text'].indexOf(card.visibleContentType)
          }]
        }, {
          name: 'hiddenContentType',
          label: trans('hidden_content_type', {}, 'flashcard'),
          type: 'choice',
          required: true,
          onChange: (newType) => {
            if (!props.card) return
            const newCard = JSON.parse(JSON.stringify(props.card))
            newCard.hiddenContentType = newType
            newCard.hiddenContent = newType === 'text' ? '' : null
            props.reset(newCard)
          },
          options: {
            condensed: true,
            choices: {
              text: trans('text'),
              image: trans('image'),
              video: trans('video'),
              audio: trans('audio')
            }
          },
          linked: [{
            name: 'hiddenContent',
            type: 'file',
            label: trans('file'),
            hideLabel: true,
            displayed: (card) => -1 !== hiddenContentTypes.indexOf(card.hiddenContentType),
            options: {
              types: hiddenContentTypes
            }
          }, {
            name: 'hiddenContent',
            label: trans('hidden_content', {}, 'flashcard'),
            type: 'html',
            displayed: (card) => -1 !== ['text'].indexOf(card.hiddenContentType),
            required: true
          }]
        }]
      }]}
    />

    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={CALLBACK_BUTTON}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
      primary={true}
    />
  </Modal>
}

CardModal.propTypes = {
  card: T.shape(
    CardTypes.propTypes
  ),
  isNew: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  formData: T.shape(
    CardTypes.propTypes
  ).isRequired,
  reset: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CardModal
}
