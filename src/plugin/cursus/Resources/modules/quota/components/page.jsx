import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

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
      title={props.quota.organization.name}
      toolbar="fullscreen more"
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
