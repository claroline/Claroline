import React, {Fragment, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {trans, transChoice} from '#/main/app/intl/translation'

import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {SessionCard} from '#/plugin/cursus/session/components/card'
import {ConfirmModal} from '#/main/app/modals/confirm/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/plugin/cursus/session/modals/cancel/store'

const SessionCancelModal = props => {
  const [sendMail, setSendMail] = useState(false)

  return (
    <ConfirmModal
      {...omit(props)}
      icon="fa fa-fw fa-ban"
      title={transChoice('cancel_session_title', props.sessions.length, {count: props.sessions.length}, 'actions')}
      subtitle={1 === props.sessions.length ? props.sessions[0].name : transChoice('count_elements', props.sessions.length, {count: props.sessions.length})}
      question={transChoice('cancel_session_message', props.sessions.length, {count: props.sessions.length}, 'actions')}
      size="lg"
      additional={
        <Fragment>
          <div className="modal-body">
            {props.sessions.map(session =>
              <SessionCard
                key={session.id}
                orientation="row"
                size="xs"
                data={session}
              />
            )}

            <FormData
              flush={true}
              name={selectors.STORE_NAME}
              className="mt-3"
              definition={[
                {
                  title: trans(''),
                  hideTitle: true,
                  fields: [
                    {
                      name: 'meta.cancelReason',
                      label: trans('cancel_session_reason', {}, 'actions'),
                      type: 'string'
                    }, {
                      name: 'canceledTemplate',
                      label: trans('cancel_session_template', {}, 'actions'),
                      type: 'template',
                      displayed: sendMail,
                      options: {
                        picker: {
                          filters: [{
                            property: 'typeName',
                            value: 'training_session_canceled',
                            locked: true
                          }]
                        }
                      }
                    }
                  ]
                }
              ]}
            />
          </div>

          <div className="modal-footer">
            <Checkbox
              id="send-cancel-mail"
              label={trans('send_cancel_mail', {}, 'actions')}
              onChange={setSendMail}
              checked={sendMail}
              inline={true}
            />
          </div>
        </Fragment>
      }
      confirmAction={{
        type: ASYNC_BUTTON,
        label: trans('confirm', {}, 'actions'),
        request: {
          url: url(['apiv2_cursus_session_cancel']),
          request: {
            method: 'POST',
            body: JSON.stringify({
              ids: props.sessions.map(session => session.id),
              cancelReason: get(props.formData, 'meta.cancelReason'),
              canceledTemplate: get(props.formData, 'canceledTemplate')
            })
          }
        }
      }}
    />
  )
}

SessionCancelModal.propTypes = {
  formData: T.object.isRequired,
  sessions:  T.arrayOf(T.shape(
    SessionTypes.propTypes
  ))
}

export {
  SessionCancelModal
}
