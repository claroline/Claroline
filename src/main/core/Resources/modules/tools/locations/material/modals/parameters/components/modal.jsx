import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/tools/locations/material/modals/parameters/store'
import {Material as MaterialTypes} from '#/main/core/tools/locations/prop-types'

const MaterialParametersModal = props =>
  <Modal
    {...omit(props, 'material', 'saveEnabled', 'loadMaterial', 'saveMaterial', 'onSave')}
    icon={props.material && props.material.id ? 'fa fa-fw fa-cog' : 'fa fa-fw fa-plus'}
    title={trans('material', {}, 'location')}
    subtitle={props.material && props.material.id ? props.material.name : trans('new_material', {}, 'location')}
    onEntering={() => props.loadMaterial(props.material)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }, {
              name: 'quantity',
              type: 'number',
              label: trans('quantity'),
              required: true,
              options: {
                min: 0
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-info',
          title: trans('information'),
          fields: [
            {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'location',
              type: 'location',
              label: trans('location')
            }, {
              name: 'organizations',
              type: 'organizations',
              label: trans('organizations'),
              displayed: false
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              type: 'image',
              label: trans('poster')
            }, {
              name: 'thumbnail',
              type: 'image',
              label: trans('thumbnail')
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn"
        type={CALLBACK_BUTTON}
        htmlType="submit"
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => props.saveMaterial(props.material ? props.material.id : null, (data) => {
          props.onSave(data)
          props.fadeModal()
        })}
      />
    </FormData>
  </Modal>

MaterialParametersModal.propTypes = {
  material: T.shape(
    MaterialTypes.propTypes
  ),
  saveEnabled: T.bool.isRequired,
  loadMaterial: T.func.isRequired,
  saveMaterial: T.func.isRequired,
  onSave: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  MaterialParametersModal
}
