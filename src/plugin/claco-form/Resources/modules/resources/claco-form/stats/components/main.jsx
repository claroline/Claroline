import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {FormStats} from '#/main/app/content/form/stats/components/main'
import {ContentTitle} from '#/main/app/content/components/title'
import {ResourcePage} from '#/main/core/resource'

const StatsMain = (props) =>
  <ResourcePage>
    <ContentTitle className="mt-3" level={2} title={trans('statistics')} />

    <ContentInfoBlocks
      className="my-4"
      size="lg"
      items={[
        {
          icon: 'fa fa-file',
          label: trans('entries', {}, 'clacoform'),
          value: get(props.stats, 'total')
        }, {
          icon: 'fa fa-user',
          label: trans('users'),
          value: get(props.stats, 'users')
        }
      ]}
    />

    <FormStats stats={props.stats} className="mb-3" />
  </ResourcePage>

StatsMain.propTypes = {
  stats: T.shape({
    total: T.number,
    users: T.number,
    fields: T.arrayOf(T.shape({
      field: T.object.isRequired,
      values: T.array
    }))
  })
}

export {
  StatsMain
}
