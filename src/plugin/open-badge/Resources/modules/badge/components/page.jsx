import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/plugin/open-badge/badge/utils'
import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'
import {ContentLoader} from '#/main/app/content/components/loader'

const Badge = (props) =>
  <ToolPage
    size="xl"
    meta={{
      title: trans('badge_name', {name: get(props.badge, 'name', trans('loading'))}, 'badge'),
      description: get(props.badge, 'meta.description')
    }}
    breadcrumb={props.badge ? props.breadcrumb : []}
    icon={
      <BadgeImage badge={props.badge} size="xl" />
    }
    title={get(props.badge, 'name', trans('loading'))}
    primaryAction="grant"
    poster={get(props.badge, 'poster')}
    actions={!isEmpty(props.badge) ? getActions([props.badge], {
      add: () => props.reload(props.badge.id),
      update: () => props.reload(props.badge.id),
      delete: () => props.reload(props.badge.id)
    }, props.path, props.currentUser) : []}
  >
    {isEmpty(props.badge) &&
      <ContentLoader
        size="lg"
        description={trans('badge_loading', {}, 'badge')}
      />
    }

    {!isEmpty(props.badge) && props.children}
  </ToolPage>

Badge.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

Badge.defaultProps = {
  breadcrumb: []
}

const BadgePage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Badge)

export {
  BadgePage
}
