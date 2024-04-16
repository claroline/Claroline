import React from 'react'
import {connect} from 'react-redux'

import {Button} from '#/main/app/action'
import {Alert} from '#/main/app/components/alert'
import {displayDate, trans} from '#/main/app/intl'
import {MODAL_LOGIN} from '#/main/app/modals/login'
import {Form} from '#/main/app/content/form/components/form'
import {ContentHtml} from '#/main/app/content/components/html'
import {MODAL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors, actions} from '#/plugin/cursus/tools/presence/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

const SignPresenceComponent = (props) =>
  <ContentSizing size="md" className="d-flex flex-column align-items-center">
    { props.currentUser && props.eventLoaded && props.currentEvent &&
      <Form
        className="mt-5"
      >
        <div className="bg-body-secondary rounded-2 p-4">
          <ContentHtml className="text-center mb-3">
            {trans('presence_info', {
              user: props.currentUser.name,
              event_title: props.currentEvent.name,
              event_datetime_start: '<span class="fw-bold">' + displayDate(props.currentEvent.start, true, true) + '</span>',
              event_datetime_end: '<span class="fw-bold">' + displayDate(props.currentEvent.end, true, true) + '</span>'
            }, 'presence')}
          </ContentHtml>

          <ContentSizing size="sm" className="d-flex flex-column align-items-center">
            <input
              className="form-control"
              placeholder={trans('event_presence_label', {}, 'presence')}
              onChange={(event) => {props.setSignature(event.target.value.trim())}}
            />

            <Button
              className="btn btn-primary mt-3"
              type={CALLBACK_BUTTON}
              label={trans('validate', {}, 'presence')}
              primary={true}
              disabled={props.signature.trim().length <= 0}
              callback={() => {props.signPresence(props.currentEvent, props.signature)}}
            />
          </ContentSizing>
        </div>
      </Form>
    }

    { !props.currentUser && props.eventLoaded && props.currentEvent &&
        <Alert
          type="warning"
          title={trans('not_registered', {}, 'presence')}
        >
          {trans('not_registered_desc', {}, 'presence')}
          <div className="btn-toolbar gap-1 mt-3 justify-content-end">
            <Button
              className={'btn btn-outline-warning'}
              label={trans('login', {}, 'actions')}
              type={MODAL_BUTTON}
              modal={[MODAL_LOGIN, {
                onLogin: () => {
                  props.history.push(`${props.path}/${props.currentEvent.codeEmargement}`)
                }
              }]}
            />
          </div>
        </Alert>
    }

    { props.eventLoaded && !props.currentEvent &&
        <Alert
          type="warning"
          title={trans('event_not_found', {}, 'presence')}
        >
          {trans('event_not_found_desc', {}, 'presence')}
          <div className="btn-toolbar gap-1 mt-3 justify-content-end">
            <Button
              className={'btn btn-outline-warning'}
              label={trans('event_not_found_retry', {}, 'presence')}
              type={CALLBACK_BUTTON}
              callback={() => props.history.push(`${props.path}`)}
            />
          </div>
        </Alert>
    }
  </ContentSizing>

const SignPresence = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    currentEvent: selectors.currentEvent(state),
    eventLoaded: selectors.eventLoaded(state),
    signature: selectors.signature(state)
  }),
  (dispatch) => ({
    signPresence: (event, signature) => {
      dispatch(actions.signPresence(event, signature))
    },
    setSignature: (sign) => {
      dispatch(actions.setSignature(sign))
    }
  })
)(SignPresenceComponent)

export {
  SignPresence
}
