import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {UrlButton} from '#/main/app/buttons/url'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {
  Table,
  TableHeaderCell,
  TableRow,
  TableCell
} from '#/main/app/content/components/table'

const BBBRooms = (props) =>
  <Fragment>
    <ContentTitle level={2} title={trans('meetings', {}, 'bbb')} />

    {0 === props.meetings.length &&
      <ContentPlaceholder
        size="lg"
        icon="fa fa-fw fa-chalkboard"
        title={trans('no_active_meeting', {}, 'bbb')}
      />
    }

    {0 < props.meetings.length &&
      <Table className="data-table">
        <thead>
          <TableRow>
            <TableHeaderCell align={'left'}>
              {trans('name')}
            </TableHeaderCell>
            <TableHeaderCell align={'left'}>
              {trans('participants')}
            </TableHeaderCell>
            <TableHeaderCell align={'left'}>
              {trans('opened', {}, 'bbb')}
            </TableHeaderCell>
            <TableHeaderCell align={'left'}>
              {trans('server')}
            </TableHeaderCell>
          </TableRow>
        </thead>

        <tbody>
        {props.meetings.map(meeting =>
          <TableRow key={meeting.meetingID}>
            <TableCell align="left">
              <UrlButton className="list-primary-action" target={meeting.url}>
                {meeting.meetingName}
              </UrlButton>
            </TableCell>
            <TableCell align="right">
              {meeting.participantCount}
            </TableCell>
            <TableCell className="boolean-cell">
              <span
                aria-hidden={true}
                className={classes('fa fa-fw', {
                  'fa-check true': meeting.running,
                  'fa-times false': !meeting.running
                })}
              />
              <span className="sr-only">{meeting.running}</span>
            </TableCell>
            <TableCell>
              {meeting.server}
            </TableCell>
          </TableRow>
        )}
        </tbody>
      </Table>
    }
  </Fragment>

BBBRooms.propTypes = {
  meetings: T.arrayOf(T.shape({
    meetingID: T.string.isRequired,
    meetingName: T.string,
    createTime: T.string,
    createDate: T.string,
    running: T.bool.isRequired,
    participantCount: T.number,
    url: T.string,
    server: T.string
  }))
}

export {
  BBBRooms
}
