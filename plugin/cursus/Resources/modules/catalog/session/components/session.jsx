import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {DetailsData} from '#/main/app/content/details/components/data'

import {
  PageContainer,
  PageHeader,
  PageActions,
  PageAction,
  PageContent
} from '#/main/core/layout/page'

import {
  Session as SessionType,
  SessionUser as SessionUserType,
  SessionQueue as SessionQueueType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {actions, selectors} from '#/plugin/cursus/catalog/session/store'

const SessionComponent = (props) => props.session && props.session.meta && props.session.meta.course ?
  <PageContainer id="catalog-session">
    <PageHeader title={props.session.name}>
      {props.currentUser && props.session.registration.publicRegistration && !props.sessionUser &&
        <PageActions>
          <PageAction
            type={CALLBACK_BUTTON}
            icon="fa fa-sign-in"
            label={trans('register')}
            primary={true}
            disabled={props.isFull || props.sessionQueue}
            callback={() => props.register(props.session.id)}
          />
        </PageActions>
      }
      {props.sessionUser && props.session.meta.workspace &&
        <PageActions>
          <PageAction
            type={URL_BUTTON}
            icon="fa fa-book"
            label={trans('workspace')}
            primary={true}
            target={['claro_workspace_open', {workspaceId: props.session.meta.workspace.id}]}
          />
        </PageActions>
      }
    </PageHeader>

    <PageContent>
      {props.sessionQueue &&
        <div className="alert alert-info">
          {trans('registration_pending_for_validation', {}, 'cursus')}
        </div>
      }
      <DetailsData
        data={props.session}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'name',
                label: trans('name'),
                type: 'string'
              }, {
                name: 'code',
                label: trans('code'),
                type: 'string'
              }, {
                name: 'meta.course.title',
                label: trans('course', {}, 'cursus'),
                type: 'string'
              }, {
                name: 'description',
                label: trans('description'),
                type: 'html'
              }, {
                name: 'restrictions.dates[0]',
                label: trans('start_date'),
                type: 'date'
              }, {
                name: 'restrictions.dates[1]',
                label: trans('end_date'),
                type: 'date'
              }, {
                name: 'restrictions.maxUsers',
                type: 'number',
                label: trans('maxUsers')
              }
            ]
          }
        ]}
      />
    </PageContent>
  </PageContainer> :
  null

SessionComponent.propTypes = {
  currentUser: T.object,
  session: T.shape(SessionType.propTypes).isRequired,
  sessionUser: T.shape(SessionUserType.propTypes),
  sessionQueue: T.shape(SessionQueueType.propTypes),
  isFull: T.bool.isRequired,
  register: T.func.isRequired
}

const Session = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    session: selectors.session(state),
    sessionUser: selectors.sessionUser(state),
    sessionQueue: selectors.sessionQueue(state),
    isFull: selectors.isFull(state)
  }),
  (dispatch) => ({
    register(sessionId) {
      dispatch(actions.register(sessionId))
    }
  })
)(SessionComponent)

export {
  Session
}
