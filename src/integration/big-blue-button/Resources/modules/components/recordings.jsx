import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDate, displayDuration, getTimeDiff} from '#/main/app/intl/date'
import {URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

const Recordings = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    delete={{
      url: props.delete
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        calculated: (row) => trans('recorded_at', {date: displayDate(row.startTime, true, true)}, 'bbb'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'startTime',
        label: trans('start_date'),
        type: 'date',
        options: {time: true},
        filterable: false
      }, {
        name: 'endTime',
        label: trans('end_date'),
        type: 'date',
        options: {time: true},
        filterable: false
      }, {
        name: 'duration',
        label: trans('duration'),
        calculated: (row) => displayDuration(getTimeDiff(row.startTime, row.endTime)),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'participants',
        label: trans('participants'),
        type: 'number',
        displayed: true
      }
    ].concat(props.customDefinition)}

    primaryAction={props.primaryAction}
    actions={(rows) => [
      {
        name: 'show-presentation',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-video',
        label: trans('show_presentation', {}, 'bbb'),
        target: get(rows[0], 'medias.presentation'),
        open: '_blank',
        displayed: !!get(rows[0], 'medias.presentation'),
        scope: ['object']
      }, {
        name: 'show-podcast',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-podcast',
        label: trans('show_podcast', {}, 'bbb'),
        target: get(rows[0], 'medias.podcast'),
        open: '_blank',
        displayed: !!get(rows[0], 'medias.podcast'),
        scope: ['object']
      }
    ].concat(props.actions(rows))}
  />

Recordings.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  delete: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // TODO : data list prop types
  })),
  primaryAction: T.func,
  actions: T.func
}

Recordings.defaultProps = {
  customDefinition: [],
  actions: () => []
}

export {
  Recordings
}
