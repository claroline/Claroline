import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {t, trans} from '#/main/core/translation'

import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {ConditionalSet} from '#/main/core/layout/form/components/fieldset/conditional-set.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {DateGroup}  from '#/main/core/layout/form/components/group/date-group.jsx'
import {HtmlGroup}  from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'
import {RadiosGroup}  from '#/main/core/layout/form/components/group/radios-group.jsx'
import {CheckboxesGroup}  from '#/main/core/layout/form/components/group/checkboxes-group.jsx'

import {Announcement as AnnouncementTypes} from './../prop-types'
import {select} from './../selectors'
import {actions} from './../actions'

const AnnounceForm = props =>
  <form>
    <div className="panel panel-default">
      <fieldset className="panel-body">
        <h2 className="sr-only">General properties</h2>

        <TextGroup
          id="announcement-title"
          label={t('title')}
          value={props.announcement.title || ''}
          onChange={value => props.updateProperty('title', value)}
          warnOnly={!props.validating}
        />

        <HtmlGroup
          id="announcement-content"
          label={t('content')}
          value={props.announcement.content}
          onChange={value => props.updateProperty('content', value)}
          minRows={10}
          warnOnly={!props.validating}
          error={get(props.errors, 'content')}
        />

        <TextGroup
          id="announcement-author"
          label={t('author')}
          value={props.announcement.meta.author || ''}
          onChange={value => props.updateProperty('meta.author', value)}
          warnOnly={!props.validating}
        />
      </fieldset>
    </div>

    <FormSections level={2}>
      <FormSection
        id="announcement-restrictions"
        icon="fa fa-fw fa-key"
        title={t('access_restrictions')}
      >
        <CheckGroup
          id="announcement-visible"
          label={trans('announcement_is_not_visible', {}, 'announcement')}
          labelChecked={trans('announcement_is_visible', {}, 'announcement')}
          value={props.announcement.restrictions.visible}
          onChange={() => props.updateProperty('restrictions.visible', !props.announcement.restrictions.visible)}
          warnOnly={!props.validating}
        />

        <ActivableSet
          id="access-dates"
          label={t('restrict_by_dates')}
          activated={!isEmpty(props.announcement.restrictions.visibleFrom) || !isEmpty(props.announcement.restrictions.visibleUntil)}
          onChange={activated => {
            if (!activated) {
              props.updateProperty('restrictions.visibleFrom', null)
              props.updateProperty('restrictions.visibleUntil', null)
            }
          }}
        >
          <div className="row">
            <DateGroup
              id="announcement-visible-from"
              className="col-md-6 col-xs-6 form-last"
              label={trans('announcement_visible_from', {}, 'announcement')}
              value={props.announcement.restrictions.visibleFrom}
              onChange={(date) => props.updateProperty('restrictions.visibleFrom', date)}
              time={true}
              warnOnly={!props.validating}
            />

            <DateGroup
              id="announcement-visible-until"
              className="col-md-6 col-xs-6 form-last"
              label={trans('announcement_visible_until', {}, 'announcement')}
              value={props.announcement.restrictions.visibleUntil}
              onChange={(date) => props.updateProperty('restrictions.visibleUntil', date)}
              time={true}
              warnOnly={!props.validating}
            />
          </div>
        </ActivableSet>
      </FormSection>
      <FormSection
        id="announcement-sending"
        icon="fa fa-fw fa-paper-plane-o"
        title={trans('announcement_sending', {}, 'announcement')}
      >
        <RadiosGroup
          id="announcement-notify-users"
          label={trans('announcement_notify_users', {}, 'announcement')}
          options={[
            {value: 0, label: trans('do_not_send', {}, 'announcement')},
            {value: 1, label: trans('send_directly', {}, 'announcement')},
            {value: 2, label: trans('send_at_predefined_date', {}, 'announcement')}
          ]}
          value={props.announcement.meta.notifyUsers}
          onChange={value => {
            props.updateProperty('meta.notifyUsers', value)

            if (value === 2 && !props.announcement.meta.notificationDate && props.announcement.restrictions.visibleFrom) {
              props.updateProperty('meta.notificationDate', props.announcement.restrictions.visibleFrom)
            }
          }}
        />

        <ConditionalSet condition={0 !== props.announcement.meta.notifyUsers}>
          <CheckboxesGroup
            id="announcement-sending-roles"
            label={trans('roles_to_send_to', {}, 'announcement')}
            options={props.workspaceRoles.map(r => ({
              value: r.id,
              label: t(r.translationKey)
            }))}
            inline={false}
            value={props.announcement.roles}
            onChange={values => {
              props.updateProperty('roles', values)
            }}
            warnOnly={!props.validating}
            error={get(props.errors, 'roles')}
          />

          {props.announcement.meta.notifyUsers === 2 &&
            <DateGroup
              id="announcement-sending-date"
              label={trans('announcement_sending_date', {}, 'announcement')}
              value={props.announcement.meta.notificationDate}
              onChange={(date) => props.updateProperty('meta.notificationDate', date)}
              time={true}
              warnOnly={!props.validating}
              error={get(props.errors, 'meta.notificationDate')}
            />
          }
        </ConditionalSet>
      </FormSection>
    </FormSections>
  </form>

AnnounceForm.propTypes = {
  errors: T.object,
  validating: T.bool,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspaceRoles: T.arrayOf(T.shape({
    id: T.string.isRequired,
    translationKey: T.string.isRequired
  })).isRequired,
  updateProperty: T.func.isRequired
}

AnnounceForm.defaultProps = {
  announcement: AnnouncementTypes.defaultProps
}

function mapStateToProps(state) {
  return {
    announcement: select.formData(state),
    errors: select.formErrors(state),
    validating: select.formValidating(state),
    workspaceRoles: select.workspaceRoles(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateProperty(prop, value) {
      dispatch(actions.updateForm(prop, value))
    }
  }
}

const ConnectedAnnounceForm = connect(mapStateToProps, mapDispatchToProps)(AnnounceForm)

export {
  ConnectedAnnounceForm as AnnounceForm
}
