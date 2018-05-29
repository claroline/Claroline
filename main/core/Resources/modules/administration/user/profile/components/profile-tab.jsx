import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {ProfileNav} from '#/main/core/user/profile/components/nav.jsx'
import {ProfileFacets} from '#/main/core/user/profile/components/facets.jsx'

import {ProfileFacet} from '#/main/core/administration/user/profile/components/facet.jsx'
import {actions} from '#/main/core/administration/user/profile/actions'
import {select} from '#/main/core/administration/user/profile/selectors'

const ProfileTabActions = () =>
  <PageActions>
    <FormPageActionsContainer
      formName={select.formName}
      opened={true}
      target={['apiv2_profile_update']}
    />
  </PageActions>

const ProfileTabComponent = props =>
  <div className="row user-profile">
    <div className="user-profile-aside col-md-3">
      <ProfileNav
        prefix="/profile"
        facets={props.facets}
        actions={[
          {
            icon: 'fa fa-fw fa-trash-o',
            label: t('delete'),
            displayed: (facet) => !facet.meta || !facet.meta.main,
            action: (facet) => props.removeFacet(facet.id),
            dangerous: true
          }
        ]}
      />

      <button
        type="button"
        className="btn btn-block profile-facet-add"
        onClick={props.addFacet}
      >
        <span className="fa fa-fw fa-plus" />
        {t('profile_facet_add')}
      </button>
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
    removeFacet(id) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: t('profile_remove_facet'),
          question: t('profile_remove_facet_question'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeFacet(id))
        })
      )
    }
  })
)(ProfileTabComponent)

export {
  ProfileTabActions,
  ProfileTab
}
