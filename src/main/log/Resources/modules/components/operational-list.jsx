import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {useReducer} from '#/main/app/store/reducer'
import {ListData} from '#/main/app/content/list/containers/data'
import {makeListReducer} from '#/main/app/content/list/store'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {displayUsername} from '#/main/community/utils'
import {route} from '#/main/community/user/routing'

const LogOperationalList = (props) => {
  const reducer = makeListReducer('operationalLogs', {
    sortBy: {property: 'date', direction: -1}
  })

  useReducer('operationalLogs', reducer)

  return (
    <ListData
      {...omit(props, 'url', 'name', 'customDefinition')}

      name="operationalLogs"
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}

      definition={[
        {
          name: 'doer',
          type: 'user',
          label: trans('action'),
          displayed: true,
          sortable: false,
          primary: true,
          render: (row) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center fw-normal">
              <UserAvatar user={row.doer} size="xs" />
              <div
                role="presentation"
                dangerouslySetInnerHTML={{ __html: `<a href="${route(row.doer)}">${displayUsername(row.doer)}</a> ` + row.details }}
              />
            </div>
          )
        }, {
          name: 'date',
          label: trans('date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'event',
          type: 'translation',
          label: trans('event'),
          displayed: false,
          sortable: false,
          options: {
            domain: 'log'
          }
        }
      ].concat(props.customDefinition)}
      selectable={false}
    />
  )
}

LogOperationalList.propTypes = {
  //name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

LogOperationalList.defaultProps = {
  autoload: true,
  customDefinition: []
}

export {
  LogOperationalList
}
