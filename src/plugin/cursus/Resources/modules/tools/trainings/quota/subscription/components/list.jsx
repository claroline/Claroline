import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {SubscriptionList as SubscriptionDataList} from '#/plugin/cursus/quota/subscription/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

const SubscriptionList = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('subscriptions', {}, 'cursus'),
      target: props.path
    }]}
    subtitle={trans('subscriptions', {}, 'cursus')}
    primaryAction="export"
    actions={[
      {
        name: 'export',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export_subscription', {}, 'cursus'),
        target: `${props.path}/export`,
        group: trans('management'),
        primary: true
      }
    ]}
  >
    <SubscriptionDataList
      name={selectors.LIST_NAME}
      path={props.path}
    />
  </ToolPage>

SubscriptionList.propTypes = {
  path: T.string.isRequired
}

export {
  SubscriptionList
}
