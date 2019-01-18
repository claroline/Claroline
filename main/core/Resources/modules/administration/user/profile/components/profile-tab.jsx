import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {ProfileNav} from '#/main/core/user/profile/components/nav'
import {ProfileFacets} from '#/main/core/user/profile/components/facets'

import {ProfileFacet} from '#/main/core/administration/user/profile/components/facet'
import {actions} from '#/main/core/administration/user/profile/actions'
import {select} from '#/main/core/administration/user/profile/selectors'

const ProfileTabComponent = props =>
  <div className="row user-profile">
    <div className="user-profile-aside col-md-3">
      <ProfileNav
        prefix="/profile"
        facets={props.facets}
        actions={(facet) => [
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete'),
            displayed: (facet) => !facet.meta || !facet.meta.main,
            callback: () => props.removeFacet(facet),
            confirm: {
              title: trans('profile_remove_facet'),
              message: trans('profile_remove_facet_question')
            },
            dangerous: true
          }
        ]}
      />

      <Button
        type={CALLBACK_BUTTON}
        className="btn btn-block btn-add-facet"
        icon="fa fa-fw fa-plus"
        label={trans('profile_facet_add')}
        callback={props.addFacet}
      />
    </div>

    <div className="user-profile-content col-md-9">
      <ProfileFacets
        prefix="/profile"
        facets={props.facets}
        facetComponent={ProfileFacet}
        openFacet={props.openFacet}
      />
    </div>
  </div>

ProfileTabComponent.propTypes = {
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired,
  openFacet: T.func.isRequired,
  addFacet: T.func.isRequired,
  removeFacet: T.func.isRequired
}

const ProfileTab = connect(
  (state) => ({
    facets: select.facets(state)
  }),
  (dispatch) => ({
    openFacet(id) {
      dispatch(actions.openFacet(id))
    },
    addFacet() {
      dispatch(actions.addFacet())
    },
    removeFacet(facet) {
      dispatch(actions.removeFacet(facet.id))
    }
  })
)(ProfileTabComponent)

export {
  ProfileTab
}
