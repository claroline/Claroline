import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {actions, selectors} from '#/plugin/announcement/resources/announcement/store'

const restrictByDates = (announcement) => get(announcement, 'restrictions.enableDates') || (get(announcement, 'restrictions.dates') && 0 !== get(announcement, 'restrictions.dates').length)

const AnnounceFormComponent = props =>
  <FormData
    name={selectors.STORE_NAME+'.announcementForm'}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      target: `${props.path}/${props.announcement.id}`,
      callback: () => props.new ?
        props.saveNewForm(props.aggregateId, props.history, props.addAnnounce, props.path) :
        props.saveForm(props.aggregateId, props.announcement, props.history, props.updateAnnounce, props.path)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title')
          }, {
            name: 'content',
            type: 'html',
            label: trans('content'),
            required: true,
            options: {
              workspace: props.workspace
            }
          }, {
            name: 'meta.author',
            type: 'string',
            label: trans('author')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            label: trans('poster'),
            type: 'image'
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }, {
            name: 'restrictions.enableDates',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            calculated: restrictByDates,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.dates', [])
              }
            },
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: restrictByDates,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }
        ]
      }
    ]}
  />

AnnounceFormComponent.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  aggregateId: T.string.isRequired,
  new: T.bool.isRequired,
  announcement: T.shape(
    AnnouncementTypes.propTypes
  ).isRequired,
  workspace: T.object,
  updateProp: T.func.isRequired,
  addAnnounce: T.func.isRequired,
  updateAnnounce: T.func.isRequired,
  saveNewForm: T.func.isRequired,
  saveForm: T.func.isRequired
}

AnnounceFormComponent.defaultProps = {
  announcement: AnnouncementTypes.defaultProps
}

const AnnounceForm = withRouter(connect(
  (state) => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    new: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME+'.announcementForm')),
    announcement: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME+'.announcementForm')),
    aggregateId: selectors.aggregateId(state)
  }),
  (dispatch) => ({
    addAnnounce(announcement) {
      dispatch(actions.addAnnounce(announcement))
    },
    updateAnnounce(announcement) {
      dispatch(actions.changeAnnounce(announcement))
    },
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.announcementForm', propName, propValue))
    },
    saveNewForm(aggregateId, history, onSuccess, path) {
      dispatch(formActions.saveForm(
        selectors.STORE_NAME+'.announcementForm',
        ['claro_announcement_create', {aggregateId: aggregateId}]
      )).then(
        (announcement) => {
          onSuccess(announcement)
          history.push(`${path}/${announcement.id}`)
        }
      )
    },
    saveForm(aggregateId, announcement, history, onSuccess, path) {
      dispatch(formActions.saveForm(
        selectors.STORE_NAME+'.announcementForm',
        ['claro_announcement_update', {aggregateId: aggregateId, id: announcement.id}]
      )).then(
        (announcement) => {
          onSuccess(announcement)
          history.push(`${path}/${announcement.id}`)
        }
      )
    }
  })
)(AnnounceFormComponent))

export {
  AnnounceForm
}
