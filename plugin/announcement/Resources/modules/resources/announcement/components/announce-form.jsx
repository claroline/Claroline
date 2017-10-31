import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {t, trans} from '#/main/core/translation'

import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {DateGroup}  from '#/main/core/layout/form/components/group/date-group.jsx'
import {HtmlGroup}  from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'
import {RadioGroup}  from '#/main/core/layout/form/components/group/radio-group.jsx'
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
          controlId="announcement-title"
          label={t('title')}
          value={props.announcement.title || ''}
          onChange={value => props.updateProperty('title', value)}
          warnOnly={!props.validating}
        />

        <HtmlGroup
          controlId="announcement-content"
          label={t('content')}
          content={props.announcement.content}
          onChange={value => props.updateProperty('content', value)}
          minRows={10}
          warnOnly={!props.validating}
          error={get(props.errors, 'content')}
        />

        <TextGroup
          controlId="announcement-author"
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
          checkId="announcement-visible"
          label={trans('announcement_is_not_visible', {}, 'announcement')}
          labelChecked={trans('announcement_is_visible', {}, 'announcement')}
          checked={props.announcement.restrictions.visible}
          onChange={() => props.updateProperty('restrictions.visible', !props.announcement.restrictions.visible)}
          warnOnly={!props.validating}
        />

        <ActivableSet
          id="access-dates"
          label={t('access_dates')}
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
              controlId="announcement-visible-from"
              className="col-md-6 col-xs-6 form-last"
              label={trans('announcement_visible_from', {}, 'announcement')}
              value={props.announcement.restrictions.visibleFrom || ''}
              onChange={(date) => props.updateProperty('restrictions.visibleFrom', date)}
              warnOnly={!props.validating}
            />

            <DateGroup
              controlId="announcement-visible-until"
              className="col-md-6 col-xs-6 form-last"
              label={trans('announcement_visible_until', {}, 'announcement')}
              value={props.announcement.restrictions.visibleUntil || ''}
              onChange={(date) => props.updateProperty('restrictions.visibleUntil', date)}
              warnOnly={!props.validating}
            />
          </div>
        </ActivableSet>
      </FormSection>
      <FormSection
        id="announcement-sending"
        icon="fa fa-fw fa-envelope"
        title={trans('announcement_sending', {}, 'announcement')}
      >
        <RadioGroup
          controlId="announcement-notify-users"
          label={trans('announcement_notify_users', {}, 'announcement')}
          hideLabel={true}
          options={[
            {value: 0, label: trans('do_not_send', {}, 'announcement')},
            {value: 1, label: trans('send_directly', {}, 'announcement')},
            {value: 2, label: trans('send_at_predefined_date', {}, 'announcement')}
          ]}
          checkedValue={props.announcement.meta.notifyUsers}
          inline={true}
          onChange={value => {
            props.updateProperty('meta.notifyUsers', value)

            if (value === 2 && !props.announcement.meta.notificationDate && props.announcement.restrictions.visibleFrom) {
              props.updateProperty('meta.notificationDate', props.announcement.restrictions.visibleFrom)
            }
          }}
        />
        {props.announcement.meta.notifyUsers === 2 &&
          <DateGroup
            controlId="announcement-sending-date"
            label={trans('announcement_sending_date', {}, 'announcement')}
            value={props.announcement.meta.notificationDate || null}
            onChange={(date) => props.updateProperty('meta.notificationDate', date)}
            warnOnly={!props.validating}
            error={get(props.errors, 'meta.notificationDate')}
          />
        }
        <CheckboxesGroup
          controlId="announcement-sending-roles"
          label={trans('roles_to_send_to', {}, 'announcement')}
          options={props.workspaceRoles.map(r => {
            return {
              value: r.id,
              label: t(r.translationKey)
            }
          })}
          inline={false}
          checkedValues={props.announcement.roles.map(r => parseInt(r))}
          onChange={values => {
            props.updateProperty('roles', values)
          }}
          warnOnly={!props.validating}
          error={get(props.errors, 'roles')}
        />
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
    id: T.number.isRequired,
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
