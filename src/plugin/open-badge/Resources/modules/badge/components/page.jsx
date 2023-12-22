import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/plugin/open-badge/badge/utils'
import {route} from '#/plugin/open-badge/badge/routing'
import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'
import {ContentLoader} from '#/main/app/content/components/loader'

const Badge = (props) =>
  <ToolPage
    className="badge-page"
    meta={{
      title: trans('badge_name', {name: get(props.badge, 'name', trans('loading'))}, 'badge'),
      description: get(props.badge, 'meta.description')
    }}
    path={[
      {
        type: LINK_BUTTON,
        label: get(props.badge, 'name', trans('loading')),
        target: !isEmpty(props.badge) ? route(props.badge, props.path) : ''
      }
    ].concat(props.group ? props.breadcrumb : [])}
    icon={
      <BadgeImage badge={props.badge} className="img-thumbnail" />
    }
    subtitle={get(props.badge, 'name', trans('loading'))}
    primaryAction="grant"
    poster={get(props.badge, 'poster')}
    actions={!isEmpty(props.badge) ? getActions([props.badge], {
      add: props.reload,
      update: props.reload,
      delete: props.reload
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
