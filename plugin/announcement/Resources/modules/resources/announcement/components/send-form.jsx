import React from 'react'
import {connect} from 'react-redux'
import get from 'lodash/get'

// todo use dynamic form
import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {ConditionalSet} from '#/main/core/layout/form/components/fieldset/conditional-set.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {RadiosGroup}  from '#/main/core/layout/form/components/group/radios-group.jsx'
import {CheckboxesGroup}  from '#/main/core/layout/form/components/group/checkboxes-group.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

import {MODAL_DATA_LIST} from '#/main/core/data/list/modals'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions as listActions} from '#/main/core/data/list/actions'

import {Announcement as AnnouncementTypes} from './../prop-types'
import {select} from './../selectors'
import {actions} from './../actions'

const SendForm = props =>
  <form>
    <FormSections level={2} defaultOpened="announcement-form">
      <FormSection
        icon="fa fa-fw fa-paper-plane-o"
        title={trans('announcement_sending', {}, 'announcement')}
        id="announcement-form"
      >
        <RadiosGroup
          id="announcement-notify-users"
          label={trans('announcement_notify_users', {}, 'announcement')}
          choices={{
            0: trans('do_not_send', {}, 'announcement'),
            1: trans('send_directly', {}, 'announcement')
          }}
          value={props.announcement.meta.notifyUsers.toString()}
          onChange={value => {
            props.updateProperty('meta.notifyUsers', parseInt(value))
          }}
        />

        <ConditionalSet condition={0 !== props.announcement.meta.notifyUsers}>
          <CheckboxesGroup
            id="announcement-sending-roles"
            label={trans('roles_to_send_to', {}, 'announcement')}
            choices={props.workspaceRoles.reduce((acc, current) => {
              acc[current.id] = trans(current.translationKey)

              return acc
            }, {})}
            inline={false}
            value={props.announcement.roles}
            onChange={values => props.updateProperty('roles', values)}
            warnOnly={!props.validating}
            error={get(props.errors, 'roles')}
          />
        </ConditionalSet>
      </FormSection>
    </FormSections>

    <Button
      primary={true}
      label={trans('validate')}
      type="callback"
      className="btn"
      callback={() => {
        if (props.announcement.meta.notifyUsers !== 0) {
          props.validateSend(props.aggregateId, props.announcement)
        }
        props.history.push('/')
      }}
    />
  </form>

SendForm.defaultProps = {
  announcement: AnnouncementTypes.defaultProps
}

function mapStateToProps(state) {
  return {
    announcement: select.formData(state),
    aggregateId: select.aggregateId(state),
    errors: select.formErrors(state),
    validating: select.formValidating(state),
    workspaceRoles: select.workspaceRoles(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateProperty(prop, value) {
      dispatch(actions.updateForm(prop, value))
    },
    validateSend(aggregateId, announce) {
      //const qs = '?' + announce.roles.map(role => 'ids[]=' + role).join('&')

      dispatch(listActions.addFilter('selected.list', 'roles', announce.roles))
      dispatch(
        modalActions.showModal(MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-user',
          title: trans('send'),
          confirmText: trans('send'),
          name: 'selected.list',
          definition: UserList.definition,
          card: UserList.card,
          filters: {roles: announce.roles},
          fetch: {
            url: ['claro_announcement_validate', {aggregateId: aggregateId, id: announce.id}],
            autoload: true
          },
          handleSelect: () => {
            dispatch(actions.sendAnnounce(aggregateId, announce))
          }
        })
      )
    }
  }
}

const ConnectedSendForm = connect(mapStateToProps, mapDispatchToProps)(SendForm)

export {
  ConnectedSendForm as SendForm
}
