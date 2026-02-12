import React, {useEffect} from 'react'
import {connect} from 'react-redux'
import { useHistory } from 'react-router-dom'
import {useDispatch} from 'react-redux'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {MODAL_SECURITY} from '#/main/app/security'
import {ContentHtml} from '#/main/app/content/components/html'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {actions, reducer, selectors} from '#/plugin/cursus/presence/store'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {PageContent, PageSection} from '#/main/app/page'
import {withReducer} from '#/main/app/store/reducer'
import {ToolPage} from '#/main/core/tool'

const EventPresenceComponent = (props) => {
  const history = useHistory()
  const dispatch = useDispatch()

  useEffect(() => {
    dispatch(actions.setCode(''))
    dispatch(actions.setCurrentEvent(null))
    dispatch(actions.setEventLoaded(false))
    dispatch(actions.setUserRegistered(false))
  }, [dispatch])

  return (
    <ToolPage title={trans('presence', {}, 'tools')}>
      <PageContent>
        <PageSection size="md" className="d-flex flex-column align-items-center mt-3">
          <div className="bg-body-secondary rounded-2 p-4 text-center">
            <ContentHtml className="mb-3">
              {trans('presence_code_desc', {}, 'presence')}
            </ContentHtml>

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
            modal={[MODAL_SECURITY, {
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
          </div>
        </PageSection>
      </PageContent>
    </ToolPage>
  )
}

const EventPresence = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      code: selectors.code(state)
    }),
    (dispatch) => ({
      setCode: (code) => dispatch(actions.setCode(code))
    })
  )(EventPresenceComponent)
)

export {
  EventPresence
}

