import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {MODAL_LOGIN} from '#/main/app/modals/login'
import {Form} from '#/main/app/content/form/components/form'
import {ContentHtml} from '#/main/app/content/components/html'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors, actions} from '#/plugin/cursus/tools/presence/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'

const EventPresenceComponent = (props) =>
  <Form
    flush={true}
    embedded={true}
    size="md"
    className="align-items-center mt-5"
  >
    <h2 className="text-center mb-4">{trans('presence', {}, 'tools')}</h2>
    <div className="row py-4 bg-body-secondary rounded-2">
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
            className="btn btn-md btn-primary mt-3 mb-3"
            type={MODAL_BUTTON}
            label={trans('validate', {}, 'presence')}
            disabled={0 >= props.code}
            modal={[MODAL_LOGIN, {
              onLogin: () => {
                props.history.push(`${props.path}/${props.code}`)
              }
            }]}
            primary={true}
          />}

        {props.currentUser &&
          <Button
            className="btn btn-md btn-primary mt-3 mb-3"
            type={CALLBACK_BUTTON}
            label={trans('validate', {}, 'presence')}
            callback={() => props.history.push(`${props.path}/${props.code}`)}
            disabled={0 >= props.code}
            primary={true}
          />}
      </ContentSizing>
    </div>
  </Form>

const EventPresence = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    path: toolSelectors.path(state),
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
