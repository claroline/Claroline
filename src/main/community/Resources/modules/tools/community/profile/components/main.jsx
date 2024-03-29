import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ProfileFacet} from '#/main/community/tools/community/profile/containers/facet'
import {getMainFacet} from '#/main/community/profile/utils'
import {ContentSizing} from '#/main/app/content/components/sizing'

// TODO : redirect on facet delete

const ProfileMain = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('user_profile'),
      target: `${props.path}/profile`
    }]}
    subtitle={trans('user_profile')}
  >
    <ContentSizing size="lg" className="mt-3">
      <div className="row">
        <div className="col-md-4">
          <Vertical
            basePath={`${props.path}/parameters/profile`}
            tabs={props.facets.map(facet => ({
              icon: get(facet, 'display.icon'),
              title: facet.title,
              path: get(facet, 'meta.main') ? '' : `/${facet.id}`,
              exact: true,
              actions: [
                {
                  name: 'delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  displayed: !get(facet, 'meta.main'),
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
            className="btn btn-outline-primary w-100 btn-add-facet"
            icon="fa fa-fw fa-plus"
            label={trans('profile_facet_add')}
            callback={props.addFacet}
          />
        </div>

        <div className="user-profile-content col-md-8">
          <Routes
            routes={[
              {
                path: `${props.path}/parameters/profile/:id?`,
                onEnter: (params) => props.openFacet(params.id || getMainFacet(props.facets).id),
                component: ProfileFacet
              }
            ]}
          />
        </div>
      </div>
    </ContentSizing>
  </ToolPage>

ProfileMain.propTypes = {
  path: T.string.isRequired,
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired,
  openFacet: T.func.isRequired,
  addFacet: T.func.isRequired,
  removeFacet: T.func.isRequired
}

export {
  ProfileMain
}
