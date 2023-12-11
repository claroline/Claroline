import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {ContentTitle} from '#/main/app/content/components/title'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

const LogsTypes = () => {
  const types = useSelector(selectors.types)

  return (
    <ToolPage subtitle={trans('types', {}, 'log')}>
      <ContentSizing className="mt-3" size="md">
        {Object.keys(types).map(type =>
          <>
            <ContentTitle title={trans(type, {}, 'log')} />
            <ul className="list-group list-group-striped mb-3">
              {types[type].map(log =>
                <li key={log} className="list-group-item">
                  <b>{trans(log, {}, 'log')}</b>
                  <p className="mb-0">{trans(log+'_desc', {}, 'log')}</p>
                </li>
              )}
            </ul>
          </>
        )}
      </ContentSizing>
    </ToolPage>
  )
}

export {
  LogsTypes
}