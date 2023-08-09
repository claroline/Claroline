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
import {selectors} from '#/plugin/flashcard/resources/flashcard/editor/modals/card/store/selectors'

const CardModal = props =>
  <Modal
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
  >
    <FormData
      level={5}
      name={selectors.STORE_NAME}
      sections={[
        {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
              name: 'question',
              label: trans('question', {}, 'flashcard'),
              type: 'string',
            }, {
              name: 'visibleContent',
              label: trans('visible_content', {}, 'flashcard'),
              type: 'string',
              options: {
                long: true
              },
              required: true
            }, {
              name: 'hiddenContent',
              label: trans('hidden_content', {}, 'flashcard'),
              type: 'string',
              options: {
                long: true
              },
              required: true
            }
          ]
        }
      ]}
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
