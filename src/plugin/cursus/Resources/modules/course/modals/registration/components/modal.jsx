import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {route} from '#/plugin/cursus/routing'
import {getInfo, isFull} from '#/plugin/cursus/utils'
import {RegistrationForm} from '#/plugin/cursus/course/modals/registration/components/form'
import {selectors} from '#/plugin/cursus/course/modals/registration/store/selectors'

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
            <AlertBlock type="warning" title={trans('La session est complète.', {}, 'cursus')}>
              {trans('Vous pouvez vous inscrire en liste d\'attente ou parcourir les autres sessions.', {}, 'cursus')}
            </AlertBlock>
          </div>
        }

        <DetailsData
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

    <RegistrationForm
      name={selectors.STORE_NAME}
      sections={get(props.course, 'registration.form', [])}
      isManager={hasPermission('edit', props.course)}
    >
      {props.session &&
        <Button
          className="btn modal-btn"
          type={LINK_BUTTON}
          label={trans('show_other_sessions', {}, 'actions')}
          target={route(props.path, props.course, props.session)+'/sessions'}
          onClick={() => props.fadeModal()}
        />
      }

      <Button
        className="btn modal-btn"
        type={CALLBACK_BUTTON}
        primary={true}
        htmlType="submit"
        label={trans(!props.session || isFull(props.session) ? 'register_waiting_list' : 'self_register', {}, 'actions')}
        callback={() => {
          console.log(props.formData)

          props.register(props.course, props.session ? props.session.id : null, props.formData)
          props.fadeModal()
        }}
      />
    </RegistrationForm>
  </Modal>

RegistrationModal.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  session: T.shape(
    SessionTypes.propTypes
  ),
  formData: T.object,
  register: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  RegistrationModal
}
