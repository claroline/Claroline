import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ProfileFacets} from '#/main/community/profile/components/facets'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ProfileFacet} from '#/main/community/administration/community/profile/components/facet'
import {actions, selectors} from '#/main/community/administration/community/profile/store'

// TODO : redirect on facet delete

const ProfileTabComponent = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('user_profile'),
      target: `${props.path}/profile`
    }]}
    subtitle={trans('user_profile')}
  >
    <div className="row user-profile" style={{marginTop: 20}}>
      <div className="user-profile-aside col-md-3">
        <Vertical
          basePath={`${props.path}/profile`}
          tabs={props.facets.map(facet => ({
            icon: facet.icon,
            title: facet.title,
            path: `/${facet.id}`,
            actions: [
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                displayed: !facet.meta || !facet.meta.main,
                callback: () => props.removeFacet(facet),
                confirm: {
                  title: trans('profile_remove_facet'),
                  message: trans('profile_remove_facet_question')
                },
                dangerous: true
              }
            ]
          }))}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-emphasis btn-block btn-add-facet"
          icon="fa fa-fw fa-plus"
          label={trans('profile_facet_add')}
          callback={props.addFacet}
        />
      </div>

      <div className="user-profile-content col-md-9">
        <ProfileFacets
          prefix={`${props.path}/profile`}
          facets={props.facets}
          facetComponent={ProfileFacet}
          openFacet={props.openFacet}
        />
      </div>
    </div>
  </ToolPage>

ProfileTabComponent.propTypes = {
  path: T.string.isRequired,
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
    path: toolSelectors.path(state),
    facets: selectors.facets(state)
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
