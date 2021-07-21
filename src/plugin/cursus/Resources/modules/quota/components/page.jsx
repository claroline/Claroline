import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {route} from '#/plugin/cursus/routing'
import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'

const QuotaPage = (props) => {
  if (isEmpty(props.quota)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('training_loading', {}, 'cursus')}
      />
    )
  }

  return (
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('trainings', props.currentContext.type, props.currentContext.data), props.path)}
      title={props.quota.id}
      toolbar="edit | fullscreen more"
      actions={[
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: route(props.basePath, props.quota) + '/edit',
          displayed: hasPermission('edit', props.quota),
          primary: true
        }
      ]}
    >
      {props.children}
    </PageFull>
  )
}

QuotaPage.propTypes = {
  path: T.array,
  basePath: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['desktop']),
    data: T.object
  }).isRequired,
  primaryAction: T.string,
  actions: T.array,
  quota: T.shape(
    QuotaTypes.propTypes
  ),
  children: T.any
}

QuotaPage.defaultProps = {
  path: []
}

export {
  QuotaPage
}
