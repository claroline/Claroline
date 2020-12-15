import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'
import {Table, TableHeader, TableRow, TableHeaderCell, TableCell} from '#/main/app/content/components/table'

const BBBServers = (props) =>
  <Fragment>
    <ContentTitle level={2} title={trans('servers', {}, 'bbb')} />

    <Table>
      <TableHeader>
        <TableHeaderCell>{trans('status')}</TableHeaderCell>
        <TableHeaderCell>{trans('name')}</TableHeaderCell>
        <TableHeaderCell>{trans('participants')}</TableHeaderCell>
      </TableHeader>

      <tbody>
        {props.servers.map((server, i) =>
          <TableRow key={i}>
            <TableCell>
              {server.disabled &&
                <span className="label label-danger">{trans('disabled')}</span>
              }

              {!server.disabled && server.limit && server.participants >= server.limit &&
                <span className="label label-warning">{trans('full')}</span>
              }

              {!server.disabled && (!server.limit || server.participants < server.limit) &&
                <span className="label label-success">{trans('available')}</span>
              }
            </TableCell>
            <TableCell>{server.url}</TableCell>
            <TableCell align="right">{server.participants + (server.limit ? ' / ' + server.limit : '')}</TableCell>
          </TableRow>
        )}
      </tbody>
    </Table>
  </Fragment>

BBBServers.propTypes = {
  servers: T.arrayOf(T.shape({
    url: T.string.isRequired,
    participants: T.number,
    limit: T.number,
    disabled: T.bool
  }))
}

export {
  BBBServers
}
