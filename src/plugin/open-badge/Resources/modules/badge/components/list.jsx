import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/plugin/open-badge/badge/utils'
import {BadgeCard} from '#/plugin/open-badge/badge/components/card'

const Badges = (props) => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser)}
      definition={[
        {
          name: 'name',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          options: {long: true},
          displayed: true,
          sortable: false
        }, {
          name: 'meta.enabled',
          label: trans('enabled'),
          type: 'boolean',
          displayed: true
        }, {
          name: 'assignable',
          label: trans('assignable', {}, 'badge'),
          type: 'boolean',
          displayed: false,
          displayable: false,
          filterable: true
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          sortable: false,
          options: {
            objectClass: 'Claroline\\OpenBadgeBundle\\Entity\\BadgeClass'
          }
        }
      ].concat(props.customDefinition)}
      display={{current: listConstants.DISPLAY_LIST_SM}}

      {...omit(props, 'path', 'url', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      card={BadgeCard}
    />
  )
}

Badges.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),

  currentContext: T.object.isRequired,
  disable: T.func.isRequired,
  enable: T.func.isRequired,
  delete: T.func.isRequired
}

Badges.defaultProps = {
  customDefinition: []
}

const BadgeList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Badges)

export {
  BadgeList
}
