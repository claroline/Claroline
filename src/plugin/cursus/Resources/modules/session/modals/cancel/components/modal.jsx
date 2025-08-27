import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {selectors} from '#/plugin/cursus/session/modals/cancel/store'
import {ConfirmModal} from '#/main/app/modals/confirm/components/modal'
import {displayDateRange} from '#/main/app/intl'

const SessionCancelModal = props =>
  <ConfirmModal
    {...omit(props, 'sessions')}
    question={transChoice('cancel_session_message', props.sessions.length, {count: props.sessions.length}, 'actions')}
    dangerous={true}
    items={props.sessions.map(session => ({
      name: displayDateRange(get(session, 'dates[0]'), get(session, 'dates[1]')),
      thumbnail: session.poster
    }))}
    confirmAction={{
      type: ASYNC_BUTTON,
      label: trans('cancel_session', {}, 'actions'),
      request: {
        url: url(['apiv2_cursus_session_cancel']),
        request: {
          method: 'POST',
          body: JSON.stringify({
            ids: props.sessions.map(session => session.id),
            cancelReason: get(props.formData, 'meta.cancelReason'),
            canceledTemplate: get(props.formData, 'canceledTemplate')
          })
        },
        success: props.onCancel
      }
    }}
  >
    <FormData
      className="mt-5"
      name={selectors.STORE_NAME}
      definition={[
        {
          title: trans('general'),
          hideTitle: true,
          fields: [
            {
              name: 'meta.cancelReason',
              label: trans('cancel_session_reason', {}, 'actions'),
              type: 'html'
            }, {
              name: 'sendMail',
              label: trans('send_cancel_mail', {}, 'actions'),
              type: 'boolean',
              linked: [
                {
                  name: 'canceledTemplate',
                  label: trans('cancel_session_template', {}, 'actions'),
                  type: 'template',
                  displayed: (data) => !!data.sendMail,
                  options: {
                    templateType: 'training_session_canceled'
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  </ConfirmModal>

SessionCancelModal.propTypes = {
  formData: T.object.isRequired,
  sessions:  T.arrayOf(T.shape(
    SessionTypes.propTypes
  )),
  onCancel: T.func
}

export {
  SessionCancelModal
}
