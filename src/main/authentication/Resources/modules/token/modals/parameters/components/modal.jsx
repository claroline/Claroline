import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/authentication/token/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'token', 'isNew', 'data', 'saveEnabled', 'load', 'save', 'onSave')}
    icon={props.isNew ? 'fa fa-fw fa-plus' : 'fa fa-fw fa-cog'}
    title={props.isNew ? trans('new_token', {}, 'security') : trans('parameters')}
    subtitle={!props.isNew && props.token ? props.token.description || props.token.token : ''}
    onEntering={() => props.load(props.token)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'description',
              type: 'string',
              label: trans('description'),
              options: {
                long: true
              }
            }, {
              name: 'user',
              type: 'user',
              label: trans('user'),
              required: true,
              disabled: props.userDisabled
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
        callback={() => {
          props.save(props.data, props.isNew, props.onSave)
          props.fadeModal()
        }}
      />
    </FormData>
  </Modal>

ParametersModal.propTypes = {
  token: T.shape({
    description: T.string,
    token: T.string
  }),
  data: T.shape({
    description: T.string,
    token: T.string
  }),
  userDisabled: T.bool, // for regular users, they only can create tokens for themselves
  isNew: T.bool.isRequired,
  onSave: T.func,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  load: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
