import React from 'react'
import {connect} from 'react-redux'
import { useHistory } from 'react-router-dom'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {ToolPage} from '#/main/core/tool'
import {MODAL_LOGIN} from '#/main/app/modals/login'
import {ContentHtml} from '#/main/app/content/components/html'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors, actions} from '#/plugin/cursus/presence/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

const EventPresenceComponent = (props) => {
  const history = useHistory()

  return (
    <ToolPage
      title={trans('presence', {}, 'tools')}
    >
      <ContentSizing size="md" className="d-flex flex-column align-items-center">
        <div className="mt-5">
          <div className="bg-body-secondary rounded-2 p-4">
            <ContentHtml className="text-center mb-3">
              {trans('presence_code_desc', {}, 'presence')}
            </ContentHtml>

            <ContentSizing size="sm" className="d-flex flex-column align-items-center mt-3">
              <input
                className="form-control"
                placeholder={trans('presence_code', {}, 'presence')}
                onChange={(event) => {
                  props.setCode(event.target.value.trim())
                }}
              />

              {!props.currentUser &&
                <Button
                  className="btn btn-primary my-3"
                  type={MODAL_BUTTON}
                  label={trans('validate', {}, 'presence')}
                  disabled={0 >= props.code}
                  modal={[MODAL_LOGIN, {
                    onLogin: () => {
                      history.push(`${props.path}/${props.code}`)
                    }
                  }]}
                  primary={true}
                />
              }

              {props.currentUser &&
                <Button
                  className="btn btn-primary my-3"
                  type={CALLBACK_BUTTON}
                  label={trans('validate', {}, 'presence')}
                  callback={() => history.push(`${props.path}/${props.code}`)}
                  disabled={0 >= props.code}
                  primary={true}
                />
              }
            </ContentSizing>
          </div>
        </div>
      </ContentSizing>
    </ToolPage>
  )
}

const EventPresence = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    code: selectors.code(state)
  }),
  (dispatch) => ({
    setCode: (code) => {
      dispatch(actions.setCode(code))
    }
  })
)(EventPresenceComponent)

export {
  EventPresence
}
