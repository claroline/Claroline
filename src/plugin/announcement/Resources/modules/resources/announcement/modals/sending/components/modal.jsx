import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans, now} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {FormData} from '#/main/app/content/form/containers/data'
import {UserList} from '#/main/core/user/components/list'
import {constants as userConst} from '#/main/core/user/constants'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store'

const SendingModal = (props) =>
  <Modal
    {...omit(props, 'aggregateId', 'announcement', 'workspace', 'workspaceRoles', 'formData', 'send', 'reset', 'update', 'updateReceivers')}
    className="data-picker-modal"
    title={trans('announcement_sending', {}, 'announcement')}
    subtitle={props.announcement.title}
    icon="fa fa-fw fa-paper-plane"
    bsSize="lg"
    onEnter={() => props.reset(props.announcement, props.workspaceRoles)}
  >
    <FormData
      name={selectors.STORE_NAME+'.form'}
      level={2}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.notifyUsers',
              type: 'choice',
              label: trans('announcement_notify_users', {}, 'announcement'),
              hideLabel: true,
              required: true,
              displayed: props.schedulerEnabled,
              onChange: (notify) => {
                if (2 === notify) {
                  props.update('meta.notificationDate', now())
                } else {
                  props.update('meta.notificationDate', null)
                }
              },
              options: {
                choices: {
                  1: trans('send_directly', {}, 'announcement'),
                  2: trans('send_at_predefined_date', {}, 'announcement')
                }
              },
              linked: [
                {
                  name: 'meta.notificationDate',
                  type: 'date',
                  label: trans('date'),
                  displayed: (announcement) => 2 === get(announcement, 'meta.notifyUsers'),
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'roles',
              label: trans('roles_to_send_to', {}, 'announcement'),
              type: 'roles',
              required: true,
              options: {
                picker: {
                  filters: [
                    {property: 'type', value: userConst.ROLE_WORKSPACE},
                    {property: 'workspace', value: props.workspace.id}
                  ]
                }
              },
              onChange: (roles) => props.updateReceivers(roles.map(role => role.id))
            }
          ]
        }
      ]}
    />

    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('receivers')}
        disabled={0 === get(props.formData, 'roles', []).length}
      >
        {0 !== get(props.formData, 'roles', []).length &&
          <UserList
            name={selectors.STORE_NAME+'.receivers'}
            url={['claro_announcement_validate', {aggregateId: props.aggregateId, id: props.announcement.id}]}
            selectable={false}
            filterable={false}
          />
        }
      </FormSection>
    </FormSections>

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      label={trans(2 === get(props.formData, 'meta.notifyUsers') ? 'plan-sending' : 'send', {}, 'actions')}
      primary={true}
      callback={() => {
        props.send(props.aggregateId, props.formData)
        props.fadeModal()
      }}
    />
  </Modal>

SendingModal.propTypes = {
  aggregateId: T.string.isRequired,
  schedulerEnabled: T.bool.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspace: T.shape({
    id: T.string
  }).isRequired,
  workspaceRoles: T.array,
  formData: T.object,
  reset: T.func.isRequired,
  update: T.func.isRequired,
  send: T.func.isRequired,
  updateReceivers: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  SendingModal
}
