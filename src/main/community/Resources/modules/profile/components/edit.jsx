import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'

import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {Profile} from '#/main/community/profile/containers/main'
import {formatFormSections, getDefaultFacet, getFormDefaultSections} from '#/main/community/profile/utils'

const ProfileEdit = (props) => {
  let facet = props.facet || getDefaultFacet()
  let sections = []
  if (facet) {
    if (facet.sections) {
      sections = formatFormSections(cloneDeep(facet.sections), props.allFields, props.user, props.parameters, props.currentUser)
    }

    if (get(facet, 'meta.main')) {
      sections = [].concat(getFormDefaultSections(props.user, props.updateProp, props.isNew), sections)
    }
  }

  return (
    <Profile
      name={props.name}
      path={props.path}
      user={props.user}
    >
      <FormData
        name={props.name}
        title={facet.title}
        save={{
          type: CALLBACK_BUTTON,
          callback: () => props.save(props.isNew ?
            ['apiv2_user_create'] :
            ['apiv2_user_update', {id: props.user.id}]
          ).then(user => {
            if (props.isNew) {
              props.history.push(props.back+'/'+user.username) // slightly ugly
            }
          })
        }}
        cancel={{
          type: LINK_BUTTON,
          target: props.back,
          exact: true
        }}
        buttons={true}
        definition={sections}
      />
    </Profile>
  )
}

ProfileEdit.propTypes = {
  name: T.string.isRequired,
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  back: T.string.isRequired,
  user: T.object,
  isNew: T.bool.isRequired,
  facet: T.object,
  allFields: T.array,
  parameters: T.object,
  currentUser: T.object,
  save: T.func.isRequired,
  updateProp: T.func.isRequired
}

export {
  ProfileEdit
}
