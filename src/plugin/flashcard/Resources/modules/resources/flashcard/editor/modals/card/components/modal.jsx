import React, {useState} from 'react'
import {PropTypes as T}  from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Card} from '#/plugin/flashcard/resources/flashcard/components/card'
import {Card as CardTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/store'
import { generateInputFields } from '#/plugin/flashcard/resources/flashcard/editor/utils'

const CardModal = props => {
  const [isFlipped, setIsFlipped] = useState(false)

  return (
    <Modal
      {...omit(props, 'card', 'formData', 'isNew', 'saveEnabled', 'reset', 'save', 'update')}
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
          props.reset({
            ...CardTypes.propTypes,
            id: makeId()
          }, true)
        }
      }}
      onExiting={() => {props.reset({...CardTypes.propTypes}, true)}}
      size="lg"
    >
      <FormData
        level={5}
        flush={true}
        name={selectors.STORE_NAME}
        definition={[{
          title: trans('general'),
          fields: [{
            name: 'preview',
            label: trans('card_preview', {}, 'flashcard'),
            required: true,
            render: (card) =>
              // Ce onClick ne marche pas dans le formData
              // En dehors du formData, il marche mais les données ne sont
              // pas mise à jours en temps réel
              <div onClick={() => setIsFlipped(!isFlipped)}>
                <Card
                  card={card}
                  flipped={isFlipped}
                  mode="preview"
                />
              </div>
          }, {
            name: 'question',
            label: trans('question', {}, 'flashcard'),
            type: 'string'
          }, {
            name: 'visibleContentType',
            label: trans('visible_content_type', {}, 'flashcard'),
            type: 'choice',
            required: true,
            onChange: () => {
              props.update('visibleContent', null)
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
            linked: generateInputFields('visible')
          }, {
            name: 'hiddenContentType',
            label: trans('hidden_content_type', {}, 'flashcard'),
            type: 'choice',
            required: true,
            onChange: () => {
              props.update('hiddenContent', null)
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
            linked: generateInputFields('hidden')
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
  )
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
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CardModal
}
