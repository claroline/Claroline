import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/authentication/integration/ips/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'ip', 'isNew', 'data', 'saveEnabled', 'update', 'loadIp', 'saveIp', 'onSave')}
    icon={props.isNew ? 'fa fa-fw fa-plus' : 'fa fa-fw fa-cog'}
    title={props.isNew ? trans('new_ip', {}, 'security') : trans('parameters')}
    subtitle={!props.isNew && props.ip ? props.ip.ip : ''}
    onEntering={() => props.loadIp(props.ip)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          fields: [
            {
              name: 'range',
              type: 'boolean',
              label: trans('define_ip_range', {}, 'security'),
              onChange: (checked) => {
                if (checked) {
                  props.update('ip', [])
                } else {
                  props.update('ip', '')
                }
              },
              linked: [
                {
                  name: 'ip[0]',
                  type: 'string',
                  label: trans('start'),
                  required: true,
                  displayed: (data) => data.range
                }, {
                  name: 'ip[1]',
                  type: 'string',
                  label: trans('end'),
                  required: true,
                  displayed: (data) => data.range
                }
              ]
            }, {
              name: 'ip',
              type: 'string',
              label: trans('ip_address'),
              required: true,
              displayed: (data) => !data.range
            }, {
              name: 'user',
              type: 'user',
              label: trans('user'),
              required: true
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
          props.saveIp(props.data, props.isNew, props.onSave)
          props.fadeModal()
        }}
      />
    </FormData>
  </Modal>

ParametersModal.propTypes = {
  ip: T.shape({
    ip: T.oneOfType([T.string, T.arrayOf(T.string)]),
    user: T.object,
    range: T.bool
  }),
  data: T.shape({
    ip: T.string,
    user: T.object,
    range: T.bool
  }),
  isNew: T.bool.isRequired,
  update: T.func.isRequired,
  onSave: T.func,
  saveEnabled: T.bool.isRequired,
  saveIp: T.func.isRequired,
  loadIp: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
