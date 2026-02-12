import React, {useEffect} from 'react'
import {connect} from 'react-redux'
import {useDispatch} from 'react-redux'
import { useHistory } from 'react-router-dom'

import {Button} from '#/main/app/action'
import {Alert} from '#/main/app/components/alert'
import {displayDate, trans} from '#/main/app/intl'
import {MODAL_SECURITY, selectors as securitySelectors} from '#/main/app/security'
import {ContentHtml} from '#/main/app/content/components/html'
import {MODAL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

import {selectors, actions, reducer} from '#/plugin/cursus/presence/store'
import {PageContent, PageSection} from '#/main/app/page'
import {withReducer} from '#/main/app/store/components/withReducer'
import {ToolPage} from '#/main/core/tool'

const SignPresenceComponent = (props) => {
  const history = useHistory()

  const dispatch = useDispatch()

  useEffect(() => {
    dispatch(actions.getEventByCode(props.code))
  }, [dispatch, props.code])

  return (
    <ToolPage title={trans('presence', {}, 'tools')}>
      <PageContent>
        <PageSection size="md" className="d-flex flex-column align-items-center mt-5">
          {props.currentUser && props.eventLoaded && props.currentEvent && props.eventSigned && props.userRegistered &&
            <Alert
              type="success"
              className="content-md"
              title={trans('presence_confirm_title', {}, 'presence')}
            >
              {trans('presence_confirm_desc', {event_title: props.currentEvent.name}, 'presence')}
              <div className="btn-toolbar gap-1 mt-3 justify-content-end">
                <Button
                  className="btn btn-success"
                  label={trans('presence_confirm_other', {}, 'presence')}
                  type={CALLBACK_BUTTON}
                  callback={() => history.push(`${props.path}`)}
                />
              </div>
            </Alert>
          }

          {props.currentUser && props.eventLoaded && props.currentEvent && !props.eventSigned && props.userRegistered &&
            <div className="d-flex flex-column align-items-center">
              <div className="bg-body-secondary rounded-2 p-4 text-center">
                <ContentHtml className="mb-3">
                  {trans('presence_info', {
                    user: props.currentUser.name,
                    event_title: props.currentEvent.name,
                    event_datetime_start: '<span class="fw-bold">' + displayDate(props.currentEvent.start, true, true) + '</span>',
                    event_datetime_end: '<span class="fw-bold">' + displayDate(props.currentEvent.end, true, true) + '</span>'
                  }, 'presence')}
                </ContentHtml>
                <input
                  className="form-control"
                  placeholder={trans('event_presence_label', {}, 'presence')}
                  onChange={(event) => {props.setSignature(event.target.value.trim())}}
                />

                <Button
                  className="btn btn-primary my-3"
                  type={CALLBACK_BUTTON}
                  label={trans('validate', {}, 'presence')}
                  primary={true}
                  disabled={props.signature.trim().length <= 0}
                  callback={() => {props.signPresence(props.currentEvent, props.signature)}}
                />
              </div>
            </div>
          }

          {!props.currentUser && props.eventLoaded && props.currentEvent ? (
            <Alert
              type="warning"
              className="content-md"
              title={trans('not_registered', {}, 'presence')}
            >
              {trans('not_registered_desc', {}, 'presence')}
              <div className="btn-toolbar gap-1 mt-3 justify-content-end">
                <Button
                  className={'btn btn-outline-warning'}
                  label={trans('login', {}, 'actions')}
                  type={MODAL_BUTTON}
                  modal={[MODAL_SECURITY, {
                    onLogin: () => {
                      history.push(`${props.path}/${props.currentEvent.codeEmargement}`)
                    }
                  }]}
                />
              </div>
            </Alert>
          ): props.eventLoaded && !props.currentEvent ? (
            <Alert
              type="warning"
              className="content-md"
              title={trans('event_not_found', {}, 'presence')}
            >
              {trans('event_not_found_desc', {}, 'presence')}
              <div className="btn-toolbar gap-1 mt-3 justify-content-end">
                <Button
                  className={'btn btn-outline-warning'}
                  label={trans('event_not_found_retry', {}, 'presence')}
                  type={CALLBACK_BUTTON}
                  callback={() => history.push(`${props.path}`)}
                />
              </div>
            </Alert>
          ): !props.userRegistered && props.eventLoaded ? (
            <Alert
              type="warning"
              className="content-md"
              title={trans('event_not_registered', {}, 'presence')}
            >
              {trans('event_not_registered_desc', {}, 'presence')}
              <div className="btn-toolbar gap-1 mt-3 justify-content-end">
                <Button
                  className={'btn btn-outline-warning'}
                  label={trans('event_not_found_retry', {}, 'presence')}
                  type={CALLBACK_BUTTON}
                  callback={() => history.push(`${props.path}`)}
                />
              </div>
            </Alert>
          ): null}

        </PageSection>
      </PageContent>
    </ToolPage>
  )
}

const SignPresence = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      currentEvent: selectors.currentEvent(state),
      eventLoaded: selectors.eventLoaded(state),
      signature: selectors.signature(state),
      eventSigned: selectors.eventSigned(state),
      userRegistered: selectors.userRegistered(state)
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
)

export {
  SignPresence
}
