import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {route} from '#/plugin/cursus/routing'
import {QuotaCard} from '#/plugin/cursus/tools/trainings/quota/components/card'

const QuotaList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    delete={{
      url: ['apiv2_cursus_quota_delete_bulk'],
      displayed: () => true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: route(props.path, row)
    })}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }
    ]}
    actions={(rows) => [
      {
        name: 'edit',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        target: route(props.path, rows[0]) + '/edit',
        displayed: true,
        group: trans('management'),
        scope: ['object']
      }
    ]}
    card={QuotaCard}
    display={{
      current: listConst.DISPLAY_LIST
    }}
  />

QuotaList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array])
}

QuotaList.defaultProps = {
  url: ['apiv2_cursus_quota_list']
}

export {
  QuotaList
}
