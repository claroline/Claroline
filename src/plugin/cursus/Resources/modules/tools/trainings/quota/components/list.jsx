import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {QuotaList as QuotaDataList} from '#/plugin/cursus/quota/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

const QuotaList = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('quotas', {}, 'cursus'),
      target: props.path
    }]}
    subtitle={trans('quotas', {}, 'cursus')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_quota', {}, 'cursus'),
        target: `${props.path}/new`,
        group: trans('management'),
        primary: true
      }
    ]}
  >
    <QuotaDataList
      name={selectors.LIST_NAME}
      path={props.path}
    />
  </ToolPage>

QuotaList.propTypes = {
  path: T.string.isRequired
}

export {
  QuotaList
}
