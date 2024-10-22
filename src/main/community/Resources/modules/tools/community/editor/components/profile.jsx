import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ContentNav} from '#/main/app/content/components/nav'

import {EditorFacet} from '#/main/community/tools/community/editor/components/facet'
import {getDefaultFacet} from '#/main/community/profile/utils'
import {EditorPage} from '#/main/app/editor'

const EditorProfile = props => {
  useEffect(() => {
    if (props.loaded) {
      // load tool parameters inside the form
      props.load(props.profile)
    }
  }, [props.contextType, props.contextId, props.loaded])

  return (
    <EditorPage
      title={trans('user_profile')}
      help={trans('user_profile_help', {}, 'editor')}
    >
      <div className="row">
        <div className="col-md-4">
          <ContentNav
            className="mb-3"
            path={`${props.path}/edit/profile`}
            sections={props.facets.map(facet => ({
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
                  callback: () => props.removeFacet(props.facets, facet),
                  confirm: {
                    title: trans('profile_remove_facet'),
                    message: trans('profile_remove_facet_question')
                  },
                  dangerous: true
                }
              ]
            }))}
            type="vertical"
          />

          <Button
            type={CALLBACK_BUTTON}
            className="btn btn-outline-primary w-100 btn-add-facet"
            icon="fa fa-fw fa-plus"
            label={trans('profile_facet_add')}
            callback={() => {
              props.addFacet(props.facets)
            }}
          />
        </div>

        <div className="col-md-8">
          <Routes
            path={`${props.path}/edit`}
            routes={[
              {
                path: '/profile/:id?',
                render: (routerProps) => {
                  let currentFacetIndex = 0
                  let currentFacet
                  if (!isEmpty(props.facets)) {
                    if (routerProps.match.params.id) {
                      currentFacetIndex = props.facets.findIndex(facet => facet.id === routerProps.match.params.id)
                    } else {
                      currentFacetIndex = props.facets.findIndex(facet => !!facet.meta.main)
                    }

                    if (-1 !== currentFacetIndex) {
                      currentFacet = props.facets[currentFacetIndex]
                    }
                  }

                  if (isEmpty(currentFacet)) {
                    currentFacetIndex = 0
                    currentFacet = getDefaultFacet()
                  }

                  return (
                    <EditorFacet
                      index={currentFacetIndex}
                      facet={currentFacet}
                    />
                  )
                }
              }
            ]}
          />
        </div>
      </div>
    </EditorPage>
  )
}

EditorProfile.propTypes = {
  path: T.string.isRequired,
  loaded: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  profile: T.object,
  facets: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired,
  load: T.func.isRequired,
  addFacet: T.func.isRequired,
  removeFacet: T.func.isRequired
}

export {
  EditorProfile
}
