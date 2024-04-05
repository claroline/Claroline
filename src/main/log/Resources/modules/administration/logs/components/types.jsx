import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {ContentTitle} from '#/main/app/content/components/title'
import {ToolPage} from '#/main/core/tool'
import {ContentSizing} from '#/main/app/content/components/sizing'

const LogsTypes = () => {
  const types = useSelector(selectors.types)

  return (
    <ToolPage title={trans('parameters')}>
      <ContentSizing className="mt-3" size="md">
        <ContentTitle title={trans('functional', {}, 'log')} />
        <ul className="list-group list-group-striped mb-3">
          {types.functional.map(log =>
            <li key={log} className="list-group-item">
              <b>{trans(log, {}, 'log')}</b>
              <p className="mb-0">{trans(log+'_desc', {}, 'log')}</p>
              <span className="badge bg-secondary">{log}</span>
            </li>
          )}
        </ul>

        <ContentTitle title={trans('operational', {}, 'log')} />
        <ul className="list-group list-group-striped mb-3">
          {types.operational.map(log =>
            <li key={log} className="list-group-item">
              <b className="d-block">{trans(log, {}, 'log')}</b>
              <span className="badge bg-secondary">{log}</span>
            </li>
          )}
        </ul>

        <ContentTitle title={trans('security', {}, 'log')} />
        <ul className="list-group list-group-striped mb-3">
          {types.security.map(log =>
            <li key={log} className="list-group-item">
              <b>{trans(log, {}, 'log')}</b>
              <p className="mb-0">{trans(log+'_desc', {}, 'log')}</p>
              <span className="badge bg-secondary">{log}</span>
            </li>
          )}
        </ul>

        <ContentTitle title={trans('message', {}, 'log')} />
        <ul className="list-group list-group-striped mb-3">
          {types.message.map(log =>
            <li key={log} className="list-group-item">
              <b>{trans(log, {}, 'log')}</b>
              <p className="mb-0">{trans(log+'_desc', {}, 'log')}</p>
              <span className="badge bg-secondary">{log}</span>
            </li>
          )}
        </ul>
      </ContentSizing>
    </ToolPage>
  )
}

export {
  LogsTypes
}