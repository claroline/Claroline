import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

// todo use dynamic form
import {t, trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {DateGroup}  from '#/main/core/layout/form/components/group/date-group.jsx'
import {HtmlGroup}  from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'

import {Announcement as AnnouncementTypes} from './../prop-types'
import {select} from './../selectors'
import {actions} from './../actions'

const AnnounceForm = props =>
  <div>
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
      </FormSections>
    </form>
    <Button
      primary={true}
      label={trans('save')}
      type="callback"
      className="btn"
      callback={() => {
        props.save(props.aggregateId, props.announcement)
      }}
    />
  </div>

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
  updateProperty: T.func.isRequired,
  save: T.func.isRequired,
  aggregateId: T.string.isRequired
}

AnnounceForm.defaultProps = {
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
    save(aggregateId, announce) {
      dispatch(
        actions.saveAnnounce(aggregateId, announce)
      )
    }
  }
}

const ConnectedAnnounceForm = connect(mapStateToProps, mapDispatchToProps)(AnnounceForm)

export {
  ConnectedAnnounceForm as AnnounceForm
}
