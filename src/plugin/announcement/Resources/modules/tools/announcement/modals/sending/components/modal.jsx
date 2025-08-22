import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans, now} from '#/main/app/intl'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {UserList} from '#/main/community/user/components/list'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/prop-types'
import {selectors} from '#/plugin/announcement/tools/announcement/modals/sending/store'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const SendingModal = (props) =>
  <FormModal
    {...omit(props, 'announcement', 'workspace', 'formData', 'onSend', 'update', 'updateReceivers', 'schedulerEnabled')}
    name={selectors.STORE_NAME+'.form'}
    title={trans('announcement_sending', {}, 'announcement')}
    subtitle={props.announcement.title}
    icon="fa fa-fw fa-paper-plane"
    onEnter={() => {
      if (props.announcement.roles) {
        props.updateReceivers(props.announcement.roles)
      }
    }}
    target={['claro_announcement_update', {id: props.announcement.id}]}
    data={props.announcement}
    onSave={props.onSend}
    saveLabel={trans(2 === get(props.formData, 'meta.notifyUsers') ? 'plan-sending' : 'send', {}, 'actions')}
    definition={[
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
            type: 'role',
            options: {
              multiple: true,
              picker: {
                url: ['apiv2_workspace_list_roles', {id: props.workspace.id}]
              }
            },
            onChange: (roles) => props.updateReceivers(roles)
          }
        ]
      }
    ]}
  >
    <FormSections level={3} flush={true} className="">
      <FormSection
        id="receivers"
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('receivers')}
      >
        <UserList
          flush={true}
          name={selectors.STORE_NAME+'.receivers'}
          url={['claro_announcement_validate', {id: props.announcement.id}]}
          selectable={false}
          actions={undefined}
        />
      </FormSection>
    </FormSections>
  </FormModal>

SendingModal.propTypes = {
  schedulerEnabled: T.bool.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspace: T.shape({
    id: T.string
  }).isRequired,
  formData: T.object,
  update: T.func.isRequired,
  onSend: T.func.isRequired,
  updateReceivers: T.func.isRequired,

  // from modal
  fadeModal: T.func.isRequired
}

export {
  SendingModal
}
