import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {actions as listActions} from '#/main/app/content/list/store'

import {CourseCard} from '#/plugin/cursus/course/components/card'
import {getActions, getDefaultAction} from '#/plugin/cursus/course/utils'

const Courses = (props) => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser)}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'code',
          type: 'string',
          label: trans('code'),
          displayed: true
        }, {
          name: 'location',
          type: 'location',
          label: trans('location'),
          placeholder: trans('online_session', {}, 'cursus'),
          displayable: false,
          sortable: false
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayed: true,
          sortable: false,
          options: {
            objectClass: 'Claroline\\CursusBundle\\Entity\\Course'
          }
        }, {
          name: 'pricing.price',
          alias: 'price',
          label: trans('price'),
          type: 'currency',
          displayable: param('pricing.enabled'),
          displayed: param('pricing.enabled'),
          filterable: param('pricing.enabled'),
          sortable: param('pricing.enabled')
        }, {
          name: 'display.order',
          alias: 'order',
          type: 'number',
          label: trans('order'),
          displayable: false,
          filterable: false
        }
      ]}
      card={CourseCard}
      display={{
        current: listConst.DISPLAY_LIST
      }}
    />
  )
}

Courses.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  currentUser: T.object,
  refresher: T.object,
  invalidate: T.func.isRequired
}

Courses.defaultProps = {
  url: ['apiv2_cursus_course_list']
}

const CourseList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Courses)

export {
  CourseList
}
