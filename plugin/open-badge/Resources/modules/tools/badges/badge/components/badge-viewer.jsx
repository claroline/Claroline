import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'

import {actions}    from '#/plugin/open-badge/tools/badges/store/actions'

import {MODAL_USERS_PICKER} from '#/main/core/modals/users'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {ListData} from '#/main/app/content/list/containers/data'
import {FormSection} from '#/main/app/content/form/components/sections'

import {AssertionList} from '#/plugin/open-badge/tools/badges/assertion/components/assertion-list'
import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/badge-card'

import {
  selectors as formSelect
} from '#/main/app/content/form/store'

// TODO : add tools
const BadgeViewerComponent = (props) => {
  return (
    <div>
      <BadgeCard
        data={props.badge}
        size="sm"
        orientation="col"
      />

      {props.badge.assignable &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('users')}
          actions={[{
            displayed: props.badge.assignable,
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS_PICKER, {
              url: ['apiv2_user_list_registerable'], // maybe not the correct URL
              title: props.title,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('select', {}, 'actions'),
                callback: () => props.addUsers(props.badge.id, selected)
              })
            }]
          }]}
        >
        
          {props.badge.meta && props.badge.meta.enabled ?
            <ListData
              name="badges.current.assertions"
              fetch={{
                url: ['apiv2_badge-class_assertion', {badge: props.badge.id}],
                autoload: props.badge.id && !props.new
              }}
              primaryAction={AssertionList.open}
              delete={{
                url: ['apiv2_badge-class_remove_users', {badge: props.badge.id}]
              }}
              definition={AssertionList.definition}
              card={AssertionList.card}
            />:
            <div>{trans('badge_must_be_enabled or assignable')}</div>
          }
        </FormSection>
      }
    </div>
  )
}

const BadgeViewer = connect(
  (state) => ({
    currentContext: state.currentContext,
    badge: formSelect.data(formSelect.form(state, 'badges.current'))
  }),
  (dispatch) =>({
    save(badge, workspace, isNew) {
      dispatch(actions.save('badges.current', badge, workspace, isNew))
    },
    addUsers(badgeId, selected) {
      dispatch(actions.addUsers(badgeId, selected))
    }
  })
)(BadgeViewerComponent)

export {
  BadgeViewer
}
