import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {route} from '#/plugin/cursus/routing'
import {getInfo, isFull} from '#/plugin/cursus/utils'
import {MODAL_REGISTRATION_PARAMETERS} from '#/plugin/cursus/registration/modals/parameters'

const RegistrationModal = props =>
  <Modal
    {...omit(props, 'path', 'course', 'session', 'register')}
    icon="fa fa-fw fa-user-plus"
    title={trans('registration')}
    subtitle={getInfo(props.course, props.session, 'name')}
    poster={getInfo(props.course, props.session, 'poster')}
  >
    {!props.session &&
      <div className="modal-body">
        <AlertBlock title={trans('no_available_session', {}, 'cursus')}>
          {trans('no_available_session_help', {}, 'cursus')}
        </AlertBlock>
      </div>
    }

    {props.session &&
      <Fragment>
        {isFull(props.session) &&
          <div className="modal-body">
            <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
              {trans('Vous pouvez vous inscrire en liste d\'attente ou parcourir les autres sessions.', {}, 'cursus')}
            </AlertBlock>
          </div>
        }

        <DetailsData
          flush={true}
          data={props.session}
          definition={[
            {
              title: trans('general'),
              primary: true,
              fields: [
                {
                  name: 'restrictions.dates',
                  type: 'date-range',
                  label: trans('date')
                }, {
                  name: 'description',
                  type: 'html',
                  label: trans('description')
                }, {
                  name: 'location',
                  type: 'location',
                  label: trans('location'),
                  placeholder: trans('online_session', {}, 'cursus'),
                  displayed: (session) => !!get(session, 'location')
                }, {
                  name: 'available',
                  type: 'string',
                  label: trans('available_seats', {}, 'cursus'),
                  displayed: (session) => !!get(session, 'restrictions.users'),
                  calculated: (session) => (get(session, 'restrictions.users') - get(session, 'participants.learners')) + ' / ' + get(session, 'restrictions.users')
                }
              ]
            }
          ]}
        />
      </Fragment>
    }

    <Toolbar
      className="btn-group-vertical"
      variant="btn"
      buttonName="modal-btn"
      actions={[
        {
          name: 'show_other_sessions',
          type: LINK_BUTTON,
          label: trans('show_other_sessions', {}, 'actions'),
          target: route(props.course, props.session)+'/sessions',
          onClick: () => props.fadeModal(),
          displayed: !!props.session
        }, {
          name: 'self_register',
          type: CALLBACK_BUTTON,
          primary: true,
          label: trans(!props.session || isFull(props.session) ? 'register_waiting_list' : 'self_register', {}, 'actions'),
          callback: () => {
            props.register(props.course, props.session ? props.session.id : null)
            props.fadeModal()
          },
          size: 'lg',
          displayed: isEmpty(get(props.course, 'registration.form', []))
        }, {
          name: 'self_register',
          type: MODAL_BUTTON,
          primary: true,
          label: trans(!props.session || isFull(props.session) ? 'register_waiting_list' : 'self_register', {}, 'actions'),
          onClick: props.fadeModal,
          modal: [MODAL_REGISTRATION_PARAMETERS, {
            course: props.course,
            session: props.session,
            onSave: (registrationData) => {
              props.register(props.course, props.session ? props.session.id : null, registrationData.data)
            }
          }],
          size: 'lg',
          displayed: !isEmpty(get(props.course, 'registration.form', []))
        }
      ]}
    />
  </Modal>

RegistrationModal.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  session: T.shape(
    SessionTypes.propTypes
  ),
  register: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  RegistrationModal
}
