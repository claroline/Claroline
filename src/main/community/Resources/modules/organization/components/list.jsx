import {createElement, useState} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'
import {constants as treeConst} from '#/main/app/content/tree/constants'

import {getActions, getDefaultAction} from '#/main/community/organization/utils'
import {OrganizationCard} from '#/main/community/organization/components/card'

const OrganizationListComponent = props => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  const [displayMode, setDisplayMode] = useState(props.tree ? treeConst.DISPLAY_TREE : listConst.DEFAULT_DISPLAY_MODE)

  const display = {
    current: displayMode,
    available: merge([], listConst.DEFAULT_DISPLAY_MODES, [treeConst.DISPLAY_TREE]),
    changeDisplay: (newDisplay) => {
      if (displayMode !== newDisplay && (treeConst.DISPLAY_TREE === newDisplay || treeConst.DISPLAY_TREE === displayMode)) {
        props.invalidate(props.name)
      }

      setDisplayMode(newDisplay)
    }
  }

  return createElement(ListData, merge({
    display: display,
    primaryAction: (row) => getDefaultAction(row, refresher, props.path, props.currentUser),
    actions: (rows) => getActions(rows, refresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows))),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code')
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      },{
        name: 'meta.default',
        type: 'boolean',
        label: trans('default')
      }, {
        name: 'email',
        type: 'email',
        label: trans('email')
      }, {
        name: 'parent',
        type: 'organization',
        label: trans('parent')
      }, {
        name: 'restrictions.public',
        alias: 'public',
        type: 'boolean',
        label: trans('public')
      }
    ].concat(props.customDefinition)
  }, omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate'), {
    name: props.name,
    fetch: {
      url: treeConst.DISPLAY_TREE === displayMode ? ['apiv2_organization_list_recursive'] : props.url,
      autoload: props.autoload
    },
    card: OrganizationCard
  }))
}

OrganizationListComponent.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  tree: T.bool.isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  invalidate: T.func.isRequired,
  currentUser: T.object,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

OrganizationListComponent.defaultProps = {
  autoload: true,
  tree: false,
  customDefinition: [],
  customActions: () => []
}

const OrganizationList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(OrganizationListComponent)

export {
  OrganizationList
}
