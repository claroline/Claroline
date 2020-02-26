import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {Button} from '#/main/app/action/components/button'

import {selectors} from '#/main/core/administration/parameters/store'
import {
  IconSet as IconSetType,
  IconItem as IconItemType
} from '#/main/core/administration/parameters/icon/prop-types'

const IconItemFormModal = props =>
  <Modal
    {...omit(props, 'mimeTypes', 'iconSet', 'iconItem', 'new', 'saveEnabled', 'updateIconItem')}
    title={props.new ? trans('icon_item_creation') : trans('icon_item_edition')}
  >
    <FormData
      level={5}
      name={selectors.STORE_NAME+'.icons.item'}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'mimeTypes',
              label: trans('mime_type'),
              type: 'choice',
              required: true,
              options: {
                multiple: true,
                condensed: true,
                choices: props.mimeTypes.reduce((acc, mimeType) => {
                  acc[mimeType] = mimeType

                  return acc
                }, {})
              },
              displayed: props.new
            }, {
              name: 'mimeType',
              label: trans('mime_type'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                choices: props.mimeTypes.reduce((acc, mimeType) => {
                  acc[mimeType] = mimeType

                  return acc
                }, {})
              },
              displayed: !props.new,
              disabled: true
            }, {
              name: 'file',
              label: trans('icon'),
              type: 'file',
              options: {
                types: ['image/*'],
                autoUpload: false
              }
            }
          ]
        }
      ]}
    />
    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled || (!props.iconItem.mimeType && !props.iconItem.mimeTypes) || (!props.iconItem.relativeUrl && !props.iconItem.file)}
      callback={() => {
        props.updateIconItem(props.iconSet, props.iconItem)
        props.fadeModal()
      }}
    />
  </Modal>

IconItemFormModal.propTypes = {
  mimeTypes: T.arrayOf(T.string).isRequired,
  iconSet: T.shape(IconSetType.propTypes).isRequired,
  iconItem: T.shape(IconItemType.propTypes).isRequired,
  new: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  updateIconItem: T.func.isRequired,
  fadeModal: T.func.isRequired
}

IconItemFormModal.defaultProps = {
  mimeTypes: []
}

export {
  IconItemFormModal
}
